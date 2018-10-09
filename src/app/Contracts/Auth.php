<?php

namespace VCComponent\Laravel\User\Contracts;

use Illuminate\Http\Request;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Validators\AuthValidator;

interface Auth
{
    public function __construct(UserRepository $repository, AuthValidator $validator);
    public function authenticate(Request $request);
    public function me(Request $request);
    public function socialLogin(Request $request);
}
