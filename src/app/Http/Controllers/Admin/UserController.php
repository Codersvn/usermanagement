<?php

namespace VCComponent\Laravel\User\Http\Controllers\Admin;

use App\Entities\User;
use VCComponent\Laravel\User\Contracts\AdminUserController;
use VCComponent\Laravel\User\Http\Controllers\ApiController;
use VCComponent\Laravel\User\Traits\UserMethodsAdmin;

class UserController extends ApiController implements AdminUserController
{
    use UserMethodsAdmin;

    private $repository;
    private $validator;
    private $transformer;
    private $credential;
}
