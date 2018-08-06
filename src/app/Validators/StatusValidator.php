<?php

namespace VCComponent\Laravel\User\Validators;

use VCComponent\Laravel\User\Validators\AbstractValidator;

class StatusValidator extends AbstractValidator
{
    protected $rules = [
        'RULE_CREATE' => [
            'name' => ['required', 'max:40'],
        ],
        'RULE_UPDATE' => [
            'name' => ['required', 'max:40'],
        ],
    ];
}
