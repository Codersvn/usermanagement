<?php

namespace VCComponent\Laravel\User\Http\Controllers;

use Illuminate\Support\Facades\App;
use VCComponent\Laravel\User\Http\Controllers\ApiController;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Traits\Authenticate;
use VCComponent\Laravel\User\Validators\AuthValidator;

class AuthController extends ApiController
{
    use Authenticate;

    private $repository;
    private $validator;
    private $entity;
    private $transformer;
    private $credential;
}
