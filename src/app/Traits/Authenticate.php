<?php

namespace VCComponent\Laravel\User\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use VCComponent\Laravel\User\Events\UserLoggedInEvent;
use VCComponent\Laravel\User\Events\UserRegisteredBySocialAccountEvent;
use VCComponent\Laravel\User\Exceptions\NotFoundException;
use VCComponent\Laravel\User\Transformers\UserTransformer;

trait Authenticate
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $this->validator->isValid($credentials, 'LOGIN');

        try {
            $user = $this->repository->findByField('email', $credentials['email'])->first();

            if (!$user) {
                throw new NotFoundException('Email');
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                throw new Exception("Password does not match", 1003);
            }

            $token = JWTAuth::attempt($credentials);

            Event::fire(new UserLoggedInEvent($user));

        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return $this->response->array(compact('token'));
    }

    public function me(Request $request)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                throw new NotFoundException('User');
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['message' => 'token expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['message' => 'token invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['message' => 'token absent'], $e->getStatusCode());

        }

        if ($request->has('includes')) {
            $transformer = new UserTransformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new UserTransformer;
        }

        Cache::flush();
        return $this->response->item($user, $transformer);
    }

    private function saveOrUpdateUser($social_account, $provider, $user_id = null)
    {
        $name_items = explode(' ', $social_account->name);
        $first_name = $name_items[0];
        unset($name_items[0]);
        $last_name = implode(' ', $name_items);

        if ($user_id != null) {
            $user = $this->entity->find($user_id);
        } else {
            $user               = $this->entity;
            $user->account_type = $provider;
            $user->social_id    = $social_account->getId() ? $social_account->getId() : '';
            $user->first_name   = $first_name;
            $user->last_name    = $last_name;

            $user->email = $social_account->getEmail() ? $social_account->getEmail() : $social_account->getId();

            if ($provider == 'facebook') {
                $user->avatar = $social_account->getAvatar() ? $social_account->getAvatar() . '&width=400&height=400' : '';
            } else {
                $user->avatar = $social_account->getAvatar() ? str_replace('sz=50', 'sz=400', $social_account->getAvatar()) : '';
            }

            $user->email_verified = 1;
            $user->save();
        }

        return $user;
    }

    public function socialLogin(Request $request)
    {
        $this->validator->isValid($request, 'SOCIAL_LOGIN');

        $provider       = $request->get('provider');
        $access_token   = $request->get('access_token');
        $social_account = Socialite::driver($provider)->userFromToken($access_token);

        $user             = null;
        $check_email_user = $this->entity->where('email', $social_account->getEmail())->first();
        if (!$check_email_user) {
            $check_user = $this->entity->where('account_type', $provider)->where('social_id', $social_account->getId())->first();
            if (!$check_user) {
                $user = $this->saveOrUpdateUser($social_account, $provider);
                event(new UserRegisteredBySocialAccountEvent($user));
            } else {
                $user = $this->saveOrUpdateUser($social_account, $provider, $check_user['id']);
            }
        } else {
            $user = $this->saveOrUpdateUser($social_account, $provider, $check_email_user['id']);
        }

        $token = JWTAuth::fromUser($user);
        Event::fire(new UserLoggedInEvent($user));
        return $this->response->array(compact('token'));
    }
}
