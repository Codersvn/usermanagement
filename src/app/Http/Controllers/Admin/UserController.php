<?php

namespace VCComponent\Laravel\User\Http\Controllers\Admin;

use App\Entities\User;
use VCComponent\Laravel\User\Http\Controllers\ApiController;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Traits\UserMethodsAdmin;
use VCComponent\Laravel\User\Transformers\UserTransformer;
use VCComponent\Laravel\User\Validators\UserValidator;

class UserController extends ApiController
{
    use UserMethodsAdmin;

    private $repository;
    private $validator;
    private $transformer;
    
    public function __construct(UserRepository $repository, UserValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->middleware('jwt.auth', ['except' => []]);

        if (isset(config('user.transformers')['user'])) {
            $this->transformer = config('user.transformers.user');
        } else {
            $this->transformer = UserTransformer::class;
        }
    }
}
