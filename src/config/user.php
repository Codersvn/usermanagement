<?php

return [

    'namespace'    => env('USER_MANAGEMENT_NAMESPACE', 'user-management'),

    'transformers' => [
        'user'   => VCComponent\Laravel\User\Transformers\UserTransformer::class,
    ],

];
