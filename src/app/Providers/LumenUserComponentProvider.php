<?php

namespace VCComponent\Laravel\User\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\User\Repositories\StatusRepository;
use VCComponent\Laravel\User\Repositories\StatusRepositoryEloquent;
use VCComponent\Laravel\User\Repositories\UserRepository;
use VCComponent\Laravel\User\Repositories\UserRepositoryEloquent;

class LumenUserComponentProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind(UserRepository::class, UserRepositoryEloquent::class);
        App::bind(StatusRepository::class, StatusRepositoryEloquent::class);
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->configure('user');
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/user.php', 'user'
        );
        $this->loadViewsFrom(
            __DIR__.'/../../views', 'user_component'
        );
    }
}
