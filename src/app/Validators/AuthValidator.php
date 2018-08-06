<?php

namespace VCComponent\Laravel\User\Validators;

use VCComponent\Laravel\User\Validators\AbstractValidator;

class AuthValidator extends AbstractValidator
{
    protected $rules = [
        'LOGIN' => [
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:4'],
        ],
    ];
}
