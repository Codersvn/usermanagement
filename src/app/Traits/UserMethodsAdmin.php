<?php

namespace VCComponent\Laravel\User\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use VCComponent\Laravel\User\Entities\UserMeta;
use VCComponent\Laravel\User\Events\UserCreatedByAdminEvent;
use VCComponent\Laravel\User\Events\UserDeletedEvent;
use VCComponent\Laravel\User\Events\UserUpdatedByAdminEvent;
use VCComponent\Laravel\User\Exceptions\PermissionDeniedException;

trait UserMethodsAdmin
{
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

    public function list(Request $request)
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

        $data         = $this->filterRequestData($request, $this->repository);
        $schema_rules = $this->validator->getSchemaRules($this->repository);

        $this->validator->isValid($data['default'], 'ADMIN_CREATE_USER');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);
        if (!$this->repository->findByField('email', $request->get('email'))->isEmpty()) {
            throw new ConflictHttpException('Email already exist', null, 1001);
        }

        $user = $this->repository->create($data['default']);

        if ($request->has('role')) {
            $user = $this->repository->attachRole($request->get('role'), $user->id);
        }

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

        Event::fire(new UserCreatedByAdminEvent($user));

        return $this->response->item($user, new $this->transformer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
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

        $existsEmail = $this->repository->existsEmail($id, $request->get('email'));
        if ($existsEmail) {
            throw new ConflictHttpException("Email has exists", null, 1001);
        }

        $user = $this->repository->update($data['default'], $id);

        if ($request->has('status')) {
            $user->status = $request->get('status');
            $user->save();
        }

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                UserMeta::where([['user_id', $user->id], ['key', $key]])->update(['value' => $value]);
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

    public function status($id, Request $request)
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
