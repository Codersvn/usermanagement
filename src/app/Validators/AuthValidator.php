<?php

namespace VCComponent\Laravel\User\Validators;

use VCComponent\Laravel\User\Validators\AbstractValidator;

class AuthValidator extends AbstractValidator
{
    private $credential;
    private $rule;

    protected $rules = [
        'LOGIN'        => [
            'password' => ['required', 'min:6'],
        ],
        'SOCIAL_LOGIN' => [
            'provider'     => ['required'],
            'access_token' => ['required'],
        ],
    ];

    public function __construct()
    {
        if (config('user.auth.credential') !== null) {
            $this->credential = config('user.auth.credential');
        } else {
            $this->credential = 'email';
        }

        if (config('user.auth.rule') !== null) {
            $this->rule = config('user.auth.rule');
        } else {
            $this->rule = [];
        }

        $this->rules['LOGIN'][$this->credential] = $this->rule;
    }
}
