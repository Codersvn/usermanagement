<?php

return [

    'namespace'    => env('USER_MANAGEMENT_NAMESPACE', 'user-management'),

    'transformers' => [
        'user' => VCComponent\Laravel\User\Transformers\UserTransformer::class,
    ],

    'controllers'  => [
        'admin'    => VCComponent\Laravel\User\Http\Controllers\Admin\UserController::class,
        'frontend' => VCComponent\Laravel\User\Http\Controllers\Frontend\UserController::class,
    ],

    'auth'         => [
        'credential' => 'email',
        'rule'       => ['required', 'email'],
    ],

];
