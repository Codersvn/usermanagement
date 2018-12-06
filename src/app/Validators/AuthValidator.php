<?php

namespace VCComponent\Laravel\User\Validators;

use VCComponent\Laravel\User\Facades\VCCAuth;
use VCComponent\Laravel\User\Validators\AbstractValidator;

class AuthValidator extends AbstractValidator
{
    protected $rules = [
        'LOGIN'                => [
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ],
        'SOCIAL_LOGIN'         => [
            'provider'     => ['required'],
            'access_token' => ['required'],
        ],
        'RULE_UPDATE_AVATAR'   => [
            'avatar' => ['required'],
        ],
        'RULE_UPDATE_PASSWORD' => [
            'old_password'              => ['required'],
            'new_password'              => ['required', 'min:6', 'max:30'],
            'new_password_confirmation' => ['required'],
        ],
    ];
}
