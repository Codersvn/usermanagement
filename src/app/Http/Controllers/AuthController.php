<?php

namespace VCComponent\Laravel\User\Http\Controllers;

use VCComponent\Laravel\User\Contracts\Auth as UserAuthContract;
use VCComponent\Laravel\User\Http\Controllers\ApiController;
use VCComponent\Laravel\User\Traits\Authenticate;

class AuthController extends ApiController implements UserAuthContract
{
    use Authenticate;

    protected $repository;
    protected $validator;
    protected $entity;
    protected $transformer;
}
