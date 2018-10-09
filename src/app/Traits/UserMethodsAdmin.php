<?php

namespace VCComponent\Laravel\User\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use VCComponent\Laravel\User\Contracts\UserValidatorInterface;
use VCComponent\Laravel\User\Events\UserCreatedByAdminEvent;
use VCComponent\Laravel\User\Events\UserDeletedEvent;
use VCComponent\Laravel\User\Events\UserUpdatedByAdminEvent;
use VCComponent\Laravel\User\Exceptions\PermissionDeniedException;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Transformers\UserTransformer;

trait UserMethodsAdmin
{
    public function __construct(UserRepository $repository, UserValidatorInterface $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->middleware('jwt.auth', ['except' => []]);

        if (isset(config('user.transformers')['user'])) {
            $this->transformer = config('user.transformers.user');
        } else {
            $this->transformer = UserTransformer::class;
        }

        if (config('user.auth.credential') !== null) {
            $this->credential = config('user.auth.credential');
        } else {
            $this->credential = 'email';
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = App::make($this->repository->model());

        if ($request->has('roles')) {
            $query = $query->whereHas('roles', function ($q) use ($request) {
                $q->whereIn('slug', explode(',', $request->get('roles')));
            });
        }

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['email', 'username'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $users    = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }
        return $this->response->paginator($users, $transformer);
    }

    function list(Request $request) {
        $query = App::make($this->repository->model());

        if ($request->has('roles')) {
            $query = $query->whereHas('roles', function ($q) use ($request) {
                $q->whereIn('slug', explode(',', $request->get('roles')));
            });
        }

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['email', 'username'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);
        $users = $query->get();

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }
        return $this->response->collection($users, $transformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user->ableToCreate()) {
            throw new PermissionDeniedException();
        }

        $data           = $this->filterRequestData($request, $this->repository);
        $schema_rules   = $this->validator->getSchemaRules($this->repository);
        $no_rule_fields = $this->validator->getNoRuleFields($this->repository);

        $this->validator->isValid($data['default'], 'ADMIN_CREATE_USER');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);
        if (!$this->repository->findByField($this->credential, $request->get($this->credential))->isEmpty()) {
            throw new ConflictHttpException(ucfirst(str_replace('_', ' ', $this->credential)) . ' already exist', null, 1001);
        }

        $user = $this->repository->create($data['default']);

        $user->password = $data['default']['password'];
        if ($request->has('status')) {
            $user->status = $request->get('status');
        }
        $user->save();

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                $user->userMetas()->create([
                    'key'   => $key,
                    'value' => $value,
                ]);
            }
        }

        if (count($no_rule_fields)) {
            foreach ($no_rule_fields as $key => $value) {
                $user->userMetas()->updateOrCreate([
                    'key'   => $key,
                    'value' => null,
                ], ['value' => '']);
            }
        }

        Event::fire(new UserCreatedByAdminEvent($user));

        return $this->response->item($user, new $this->transformer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user->ableToShow($id)) {
            throw new PermissionDeniedException();
        }

        $user = $this->repository->find($id);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($user, $transformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user->ableToUpdateProfile($id)) {
            throw new PermissionDeniedException();
        }

        $data         = $this->filterRequestData($request, $this->repository);
        $schema_rules = $this->validator->getSchemaRules($this->repository);

        $this->validator->isValid($data['default'], 'ADMIN_UPDATE_USER');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);

        $existsCredential = $this->repository->existsCredential($id, $request->get($this->credential));
        if ($existsCredential) {
            throw new ConflictHttpException(ucfirst(str_replace('_', ' ', $this->credential)) . ' already exist', null, 1001);
        }

        $user = $this->repository->update($data['default'], $id);

        if ($request->has('status')) {
            $user->status = $request->get('status');
            $user->save();
        }

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                $user->userMetas()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        Event::fire(new UserUpdatedByAdminEvent($user));

        return $this->response->item($user, new $this->transformer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user->ableToDelete($id)) {
            throw new PermissionDeniedException();
        }

        $this->repository->delete($id);

        Event::fire(new UserDeletedEvent($user));

        return $this->success();
    }

    public function bulkUpdateStatus(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user->ableToUpdate()) {
            throw new PermissionDeniedException();
        }

        $this->validator->isValid($request, 'BULK_UPDATE_STATUS');

        $data = $request->all();

        $query = App::make($this->repository->model());
        $query->whereIn('id', $data['item_ids'])->update(['status' => $data['status']]);

        return $this->success();
    }

    public function status(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user->ableToUpdate()) {
            throw new PermissionDeniedException();
        }

        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $data = $request->all();

        $query = App::make($this->repository->model());
        $query->where('id', $id)->update(['status' => $data['status']]);

        return $this->success();
    }
}
