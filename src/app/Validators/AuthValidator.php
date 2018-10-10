<?php

namespace VCComponent\Laravel\User\Validators;

use VCComponent\Laravel\User\Facades\VCCAuth;
use VCComponent\Laravel\User\Validators\AbstractValidator;

class AuthValidator extends AbstractValidator
{
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
        $credentialField = VCCAuth::getCredentialField();
        $credentialRule  = VCCAuth::getCredentialRule();

        $this->rules['LOGIN'][$credentialField] = $credentialRule;
    }
}
