<?php

namespace VCComponent\Laravel\User\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\User\Auth\Auth as AuthHelper;
use VCComponent\Laravel\User\Contracts\AdminUserController;
use VCComponent\Laravel\User\Contracts\Auth;
use VCComponent\Laravel\User\Contracts\AuthHelper as AuthHelperContract;
use VCComponent\Laravel\User\Contracts\FrontendUserController;
use VCComponent\Laravel\User\Contracts\UserValidatorInterface;
use VCComponent\Laravel\User\Http\Controllers\Admin\UserController as AdminController;
use VCComponent\Laravel\User\Http\Controllers\AuthController;
use VCComponent\Laravel\User\Http\Controllers\Frontend\UserController as FrontendController;
use VCComponent\Laravel\User\Repositories\StatusRepository;
use VCComponent\Laravel\User\Repositories\StatusRepositoryEloquent;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Repositories\UserRepositoryEloquent;
use VCComponent\Laravel\User\Validators\UserValidator;

class UserComponentProvider extends ServiceProvider
{
    private $adminController;
    private $frontendController;
    private $authController;
    private $userValidator;
    private $auth;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('user.controllers.admin') === null) {
            $this->adminController = AdminController::class;
        } else {
            $this->adminController = config('user.controllers.admin');
        }

        if (config('user.controllers.frontend') === null) {
            $this->frontendController = FrontendController::class;
        } else {
            $this->frontendController = config('user.controllers.frontend');
        }

        if (config('user.controllers.auth') === null) {
            $this->authController = AuthController::class;
        } else {
            $this->authController = config('user.controllers.auth');
        }

        if (config('user.validators.user') === null) {
            $this->userValidator = UserValidator::class;
        } else {
            $this->userValidator = config('user.validators.user');
        }

        if (config('user.auth') === null) {
            $this->auth = AuthHelper::class;
        } else {
            $this->auth = config('user.auth');
        }

        App::bind(UserRepository::class, UserRepositoryEloquent::class);
        App::bind(StatusRepository::class, StatusRepositoryEloquent::class);
        App::bind(AdminUserController::class, $this->adminController);
        App::bind(FrontendUserController::class, $this->frontendController);
        App::bind(Auth::class, $this->authController);
        App::bind(UserValidatorInterface::class, $this->userValidator);
        App::bind('vcc.auth', AuthHelperContract::class);
        App::bind(AuthHelperContract::class, $this->auth);
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../migrations/' => database_path('migrations'),
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/../../config/user.php' => config_path('user.php'),
        ], 'config');
        $this->loadViewsFrom(
            __DIR__ . '/../../views', 'user_component'
        );
    }
}
