<?php

namespace VCComponent\Laravel\User\Entities;

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
        'avatar',
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
