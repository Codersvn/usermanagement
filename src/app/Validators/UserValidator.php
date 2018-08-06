<?php

namespace VCComponent\Laravel\User\Validators;

use Exception;
use Illuminate\Support\Facades\Validator;
use VCComponent\Laravel\User\Validators\AbstractValidator;

class UserValidator extends AbstractValidator
{

    protected $rules = [
        'ADMIN_CREATE_USER'  => [
            'email'      => ['required', 'email', 'max:40'],
            'password'   => ['required', 'min:6', 'max:30'],
            'username'   => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'first_name' => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'last_name'  => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
        ],
        'ADMIN_UPDATE_USER'  => [
            'email'      => ['required', 'email', 'max:40'],
            'username'   => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'first_name' => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'last_name'  => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
        ],
        'RULE_CREATE'        => [
            'email'      => ['required', 'email', 'max:40'],
            'password'   => ['required', 'min:6', 'max:30'],
            'username'   => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'first_name' => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'last_name'  => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
        ],
        'RULE_UPDATE'        => [
            'username'   => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'first_name' => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
            'last_name'  => ['required', 'regex:/[a-z0-9\s]*/i', 'max:100'],
        ],
        'BULK_UPDATE_STATUS' => [
            'item_ids' => ['required'],
            'status'   => ['required'],
        ],
        'UPDATE_STATUS_ITEM' => [
            'status' => ['required'],
        ],
        'VERIFY_EMAIL'       => [
            'token' => ['required'],
        ],
    ];

    public function getSchemaRules($repository)
    {
        $schema = collect($repository->model()::schema());
        $rules  = $schema->map(function ($item) {
            return $item['rule'];
        });
        return $rules->toArray();
    }

    public function isSchemaValid($data, $rules)
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new Exception($validator->errors(), 1000);
        }
        return true;
    }
}
