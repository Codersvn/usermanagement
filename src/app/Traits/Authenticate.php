<?php

namespace VCComponent\Laravel\User\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use VCComponent\Laravel\User\Events\UserLoggedInEvent;
use VCComponent\Laravel\User\Exceptions\NotFoundException;
use VCComponent\Laravel\User\Transformers\UserTransformer;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

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
}
