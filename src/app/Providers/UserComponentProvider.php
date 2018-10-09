<?php

namespace VCComponent\Laravel\User\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\User\Contracts\AdminUserController;
use VCComponent\Laravel\User\Contracts\FrontendUserController;
use VCComponent\Laravel\User\Http\Controllers\Admin\UserController as AdminController;
use VCComponent\Laravel\User\Http\Controllers\Frontend\UserController as FrontendController;
use VCComponent\Laravel\User\Repositories\StatusRepository;
use VCComponent\Laravel\User\Repositories\StatusRepositoryEloquent;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Repositories\UserRepositoryEloquent;

class UserComponentProvider extends ServiceProvider
{
    private $adminController;
    private $frontendController;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('user') === null && config('user.controllers.admin') === null) {
            $this->adminController = AdminController::class;
        } else {
            $this->adminController = config('user.controllers.admin');
        }

        if (config('user') === null && config('user.controllers.admin') === null) {
            $this->frontendController = FrontendController::class;
        } else {
            $this->frontendController = config('user.controllers.frontend');
        }

        App::bind(UserRepository::class, UserRepositoryEloquent::class);
        App::bind(StatusRepository::class, StatusRepositoryEloquent::class);
        App::bind(AdminUserController::class, $this->adminController);
        App::bind(FrontendUserController::class, $this->frontendController);
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
