<?php

namespace VCComponent\Laravel\User\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use VCComponent\Laravel\User\Entities\UserMeta;
use VCComponent\Laravel\User\Events\UserEmailVerifiedEvent;
use VCComponent\Laravel\User\Events\UserRegisteredEvent;
use VCComponent\Laravel\User\Events\UserUpdatedEvent;
use VCComponent\Laravel\User\Exceptions\PermissionDeniedException;
use VCComponent\Laravel\User\Notifications\UserRegisteredNotification;

trait UserMethodsFrontend
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

    public function list(Request $request) {
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
        $data         = $this->filterRequestData($request, $this->repository);
        $schema_rules = $this->validator->getSchemaRules($this->repository);

        $this->validator->isValid($data['default'], 'RULE_CREATE');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);
        if (!$this->repository->findByField('email', $request->get('email'))->isEmpty()) {
            throw new ConflictHttpException('Email already exist', null, 1001);
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

        // $user = $this->repository->attachRole('user', $user->id);

        Event::fire(new UserRegisteredEvent($user));

        $token = JWTAuth::fromUser($user);

        return $this->response->array(compact('token'));
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

        $this->validator->isValid($request, 'RULE_UPDATE');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);

        $user = $this->repository->update($data['default'], $id);

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                UserMeta::where([['user_id', $user->id], ['key', $key]])->update(['value' => $value]);
            }
        }

        Event::fire(new UserUpdatedEvent($user));

        return $this->response->item($user, new $this->transformer);
    }

    public function verifyEmail($id, Request $request)
    {
        $this->validator->isValid($request, 'VERIFY_EMAIL');

        $user = $this->repository->find($id);
        if (!Hash::check($user->email, $request->get('token'))) {
            throw new UnauthorizedHttpException("Token does not match", null, null, 1006);
        }
        $user = $this->repository->verifyEmail($user);

        Event::fire(new UserEmailVerifiedEvent($user));

        return $this->response->item($user, new $this->transformer);
    }

    public function isVerifiedEmail($id)
    {
        $user = $this->repository->find($id);
        $data = [
            'email_verified' => $user->email_verified == 1 ? true : false,
        ];
        return $this->response->array(['data' => $data]);
    }

    public function resendVerifyEmail($id)
    {
        $user = $this->repository->find($id);

        if ($user->email_verified == 1) {
            throw new ConflictHttpException("Your email address is verified", null, 1007);
        }

        $user->notify(new UserRegisteredNotification());
        
        return $this->success();
    }
}
