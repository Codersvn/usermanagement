<?php

namespace VCComponent\Laravel\User\Auth;

use VCComponent\Laravel\User\Contracts\AuthHelper as AuthHelperContract;
use VCComponent\Laravel\User\Traits\AuthHelper;

class Auth implements AuthHelperContract
{
    use AuthHelper;

    protected $field = 'email';

    protected $rule = ['required', 'email'];
}
