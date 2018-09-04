# User Component for Laravel and Lumen

The User Component package provides a convenient way of managing application's users.

## Table of content

- [Installation](#installation)
    - [Composer](#composer)
    - [Service provider](#service-provider)
        - [Laravel](#laravel)
        - [Lumen](#lumen)
    - [Config and Migration](#config-and-migration)
        - [Laravel](#laravel)
        - [Lumen](#lumen)
    - [Environment](#environment)
- [Configuration](#configuration)
    - [URL Namespace](#url-namespace)
    - [User Model](#user-model)
    - [User Transformer](#user-transformer)
        - [Laravel](#laravel)
        - [Lumen](#lumen)
    - [Social Login](#social-login)
- [User Model](#user-model)
    - [User Schema](#user-schema)
    - [User Management](#user-management)
- [APIs List](#apis-list)
- [Routing](#routing)
    - [Custom Routing](#custom-routing)
    - [Custom Controller](#custom-controller)
- [Additional Configuration](#additional-configuration)

## Installation

### Composer

To include the package in your project, Please run following command.

```
composer require codersvn/usermanagement
```
Once the package is installed, the next step is dependant on which framework you are using.

### Service provider

#### Laravel

In your `config/app.php` add the following Service Providers to the end of the `providers` array:

```php
'providers' => [
    ...
    Dingo\Api\Provider\LaravelServiceProvider::class,
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    Prettus\Repository\Providers\RepositoryServiceProvider::class,
    VCComponent\Laravel\User\Providers\UserComponentProvider::class,
    VCComponent\Laravel\User\Providers\UserComponentRouteProvider::class,
    VCComponent\Laravel\User\Providers\UserComponentEventProvider::class,
],
```

#### Lumen

In your `bootstrap/app.php` add the following Service Providers.

```php
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Dingo\Api\Provider\LumenServiceProvider::class);
$app->register(Prettus\Repository\Providers\LumenRepositoryServiceProvider::class);
$app->register(VCComponent\Laravel\User\Providers\LumenUserComponentProvider::class);
```

You also need to define `route` in `bootstrapp/app.php`.
```php
$app->router->group([
], function ($router) {
    require __DIR__ . '/../vendor/codersvn/usermanagement/src/routes.php';
});
```

### Config and Migration

#### Laravel

Run the following commands to publish configuration and migration files.

```
php artisan vendor:publish --provider="VCComponent\Laravel\User\Providers\UserComponentProvider"
php artisan vendor:publish --provider="Dingo\Api\Provider\LaravelServiceProvider"
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan vendor:publish --provider "Prettus\Repository\Providers\RepositoryServiceProvider"
```

Create tables.

```
php artisan migrate
```

> Please delete the Laravel default `users` migration file to avoid conflict when running the migrate command.

Make a change in `config/auth.php`.

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => VCComponent\Laravel\User\Entities\User::class,
    ],
],
```

#### Lumen

Create `config/auth.php` file and add the following contents.

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => VCComponent\Laravel\User\Entities\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Here you may set the options for resetting passwords including the view
    | that is your password reset e-mail. You may also set the name of the
    | table that maintains all of the reset tokens for your application.
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
```

Create the following migration files.

```
php artisan make:migration create_password_resets_table
php artisan make:migration create_users_table
php artisan make:migration create_statuses_table
php artisan make:migration create_user_meta_table
```

Add these following contents to those corresponding migration files

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}

```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 40);
            $table->string('username', 100);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('password', 100);
            $table->dateTime('last_login');
            $table->boolean('email_verified')->default(0);
            $table->integer('status');
            $table->rememberToken();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

```

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}

```

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_meta', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_meta');
    }
}
```

Create tables.

```
php artisan migrate
```

### Environment

In `.env` file, we need some configuration.

```
API_PREFIX=api
API_VERSION=v1
API_NAME="Your API Name"
API_DEBUG=false
```

Generate `JWT_SECRET` in `.env`file.

```
php artisan jwt:secret
```

Now the package is ready to use.

## Configuration

### URL Namespace

To avoid duplication with your application's api endpoints, the package has a default namespace for its routes which is `user-management`. For example:

```
{{url}}/api/user-management/admin/users
```

You can modify the package url namespace to whatever you want by modifying the `USER_MANAGEMENT_NAMESPACE` variable in `.env` file.

```
USER_MANAGEMENT_NAMESPACE="your-namespace"
```

### User Model

You can use your own `User` model by modifying `config/auth.php`

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Entities\User::class,
    ],
],
```

Your `User` model must has the following content.

```php
<?php

namespace App\Entities;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use VCComponent\Laravel\User\Contracts\UserManagement;
use VCComponent\Laravel\User\Contracts\UserSchema;
use VCComponent\Laravel\User\Notifications\MailResetPasswordToken;
use VCComponent\Laravel\User\Traits\UserManagementTrait;
use VCComponent\Laravel\User\Traits\UserSchemaTrait;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Model implements AuthenticatableContract, JWTSubject, Transformable, UserManagement, UserSchema, CanResetPasswordContract
{
    use Authenticatable,
        TransformableTrait,
        UserManagementTrait,
        UserSchemaTrait,
        // Notifiable,
        CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'username',
        'first_name',
        'last_name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getEmailVerifyToken()
    {
        return Hash::make($this->email);
    }

    public function sendPasswordResetNotification($token)
    {
        // $this->notify(new MailResetPasswordToken($token));
    }

    public function getToken()
    {
        return JWTAuth::fromUser($this);
    }
}
```

### User Transformer

#### Laravel

You can use your own `UserTransformer` class by modifying `config/user.php`.

```php
'transformers' => [
    'user'   => App\Transformers\UserTransformer::class,
],
```

#### Lumen

Create `config/user.php` with the following content.

```php
<?php

return [

    'namespace'    => env('USER_MANAGEMENT_NAMESPACE', 'user-management'),

    'transformers' => [
        'user'   => VCComponent\Laravel\User\Transformers\UserTransformer::class,
    ],

];
```

Now you can modify `UserTransformer` class.

### Social login

To be able to use social login api provided by the package, you have to add these configuartion to `app/services.php`

```php
'facebook'  => [
    'client_id'     => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect'      => env('FACEBOOK_CALLBACK_URL'),
],

'google'    => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_CALLBACK_URL'),
],
```

## User Model

### User Schema

By default, the package provides some very basic fields in `users` table. In your application, you may want to have your own fields in `users` table to meet your application's requirements. It can be solved thanks to the *User Meta* system within the package which holds any additional fields datas you include.

The package provides a way to describe your additional fields throught `schema()` method. In `schema()`, you'll need to define your field `name`, field `type` and field validation `rule`.

All you need to do is declaring `schema()` method in your `User` model.

```php
public static function schema()
{
    return [
        'address' => [
            'type' => 'string',
            'rule' => ['required']
        ],
        'phone_number' => [
            'type' => 'string',
            'rule' => ['required', 'regex:/^\d+$/', 'min:9', 'max:15']
        ],
    ];
}
```

### User Management

In your application, you may want to determine if the user is granted to access user resources. The package provides `UserManagementTrait` which contains the logic for granting access. The `UserMangementTrait` contains 5 methods: `ableToShow()`, `ableToCreate()`, `ableToUpdate()`, `ableToUpdateProfile()`, `ableToDelete()`. These methods will execute the checking logic and then return boolean value.

To overwrite the default logic with your own, all you need to do is declaring these methods within your `User` model.

```php
public function ableToUpdateProfile($id)
{
    if ($this->id == $id || $this->isRole('admin')) {
        return true;
    }
    return false;
}
```

> Take a look at `VCComponent\Laravel\User\Traits\UserManagementTrait` for more details of these methods.

## APIs List

Here is the list of APIs provided by the package.

| Verb   | URI                                               | Action              |
|--------|---------------------------------------------------|---------------------|
| POST   | `/api/{namespace}/register`                       | register            |
| POST   | `/api/{namespace}/login`                          | login               |
| POST   | `/api/{namespace}/login/social`                   | social login        |
| GET    | `/api/{namespace}/me`                             | get profile         |
| POST   | `/api/{namespace}/password/email`                 | forgot password     |
| PUT    | `/api/{namespace}/password/reset`                 | reset password      |
| ------ | ------                                            | ------              |
| GET    | `/api/{namespace}/admin/users`                    | index               |
| GET    | `/api/{namespace}/admin/users/all`                | list all            |
| POST   | `/api/{namespace}/admin/users`                    | store               |
| GET    | `/api/{namespace}/admin/users/{id}`               | show                |
| PUT    | `/api/{namespace}/admin/users/{id}`               | update              |
| DELETE | `/api/{namespace}/admin/users/{id}`               | destroy             |
| PUT    | `/api/{namespace}/admin/users/status/bulk`        | bulk update status  |
| PUT    | `/api/{namespace}/admin/users/status/{id}`        | update item status  |
| ------ | ------                                            | ------              |
| GET    | `/api/{namespace}/users`                          | index               |
| GET    | `/api/{namespace}/users/all`                      | list all            |
| GET    | `/api/{namespace}/users/{id}`                     | show                |
| PUT    | `/api/{namespace}/users/{id}`                     | update              |
| PUT    | `/api/{namespace}/users/{id}/verify-email`        | verify email        |
| GET    | `/api/{namespace}/users/{id}/is-verified-email`   | is verified email   |
| POST   | `/api/{namespace}/users/{id}/resend-verify-email` | resend verify email |

## Routing

### Custom Routing

To use your own routes, you need to **remove** `VCComponent\Laravel\User\Providers\UserComponentRouteProvider` within `config/app.php`.

Now you can manually create and use your own routes.

### Custom Controller

You are free to use your own `UserController` to customize the API functionality.

To make sure that your changes won't crash the other functionality, your `UserController` need to extend `VCComponent\Laravel\User\Http\Controller\ApiController` and use `UserAdminMethods` trait for admin controller, `UserFrontendMethods` trait for frontend controller.

## Additional Configuration

The package contains 3 other packages which are `dingo/api`, `tymon/jwt-auth`, `prettus/l5-repository`.

Other configurations of these packages, please follow their documentation.