<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['prefix' => config('user.namespace')], function ($api) {
        // Auth
        $api->post('register', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@store');
        $api->post('login', 'VCComponent\Laravel\User\Http\Controllers\AuthController@authenticate');
        $api->get('me', 'VCComponent\Laravel\User\Http\Controllers\AuthController@me');
        $api->post('password/email', 'VCComponent\Laravel\User\Http\Controllers\ForgotPasswordController@sendResetLinkEmail');
        $api->put('password/reset', 'VCComponent\Laravel\User\Http\Controllers\ResetPasswordController@reset');

        // Users
        $api->get('users', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@index');
        $api->get('users/all', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@list');
        $api->get('users/{id}', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@show');
        $api->put('users/{id}', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@update');
        $api->put('users/{id}/verify-email', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@verifyEmail');
        $api->get('users/{id}/is-verified-email', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@isVerifiedEmail');
        $api->post('users/{id}/resend-verify-email', 'VCComponent\Laravel\User\Http\Controllers\Frontend\UserController@resendVerifyEmail');

        $api->group(['prefix' => 'admin'], function ($api) {
            // Users
            $api->get('users', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@index');
            $api->get('users/all', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@list');
            $api->post('users', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@store');
            $api->get('users/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@show');
            $api->put('users/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@update');
            $api->delete('users/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@destroy');
            $api->put('users/status/bulk', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@bulkUpdateStatus');
            $api->put('users/status/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\UserController@status');

            // Statuses
            $api->get('statuses', 'VCComponent\Laravel\User\Http\Controllers\Admin\StatusController@index');
            $api->get('statuses/all', 'VCComponent\Laravel\User\Http\Controllers\Admin\StatusController@list');
            $api->get('statuses/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\StatusController@show');
            $api->post('statuses', 'VCComponent\Laravel\User\Http\Controllers\Admin\StatusController@store');
            $api->put('statuses/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\StatusController@update');
            $api->delete('statuses/{id}', 'VCComponent\Laravel\User\Http\Controllers\Admin\StatusController@destroy');
        });
    });
});
