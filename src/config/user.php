<?php

return [

    'namespace'    => env('USER_MANAGEMENT_NAMESPACE', 'user-component'),

    'transformers' => [
        'user'   => VCComponent\Laravel\User\Transformers\UserTransformer::class,
    ],

];
