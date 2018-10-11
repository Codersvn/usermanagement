<?php

namespace VCComponent\Laravel\User\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use VCComponent\Laravel\User\Contracts\AuthHelper as AuthHelperContract;
use VCComponent\Laravel\User\Exceptions\NotFoundException;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Validators\AuthValidator;

class Auth implements AuthHelperContract
{
    protected $repository;
    protected $validator;

    public function __construct(UserRepository $repository, AuthValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
    }

    public function authenticate(Request $request)
    {
        $this->validator->isValid($request, 'LOGIN');

        $user = $this->repository->findByField('email', $request->get('email'))->first();

        if (!$user) {
            throw new NotFoundException('Email');
        }

        if (!Hash::check($request->get('password'), $user->password)) {
            throw new \Exception("Password does not match", 1003);
        }

        return $user;
    }

    public function isEmpty(Request $request)
    {
        if (!$this->repository->findByField('email', $request->get('email'))->isEmpty()) {
            throw new ConflictHttpException('Email already exist', null, 1001);
        }
    }

    public function isExists(Request $request, $id)
    {
        if ($request->has('email')) {
            $user = $this->repository->findWhere([
                'email' => $request->get('email'),
                ['id', '!=', $id],
            ])->first();

            if ($user) {
                throw new ConflictHttpException('Email already exist', null, 1001);
            }
        }
    }
}
