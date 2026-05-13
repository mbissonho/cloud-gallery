<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if(Auth::attempt($request->validated())) {
            return new UserResource(Auth::user());
        }

        return response(status: 401);
    }

    public function register(RegisterRequest $request)
    {
        $validatedAttributes = $request->validated();
        $validatedAttributes['password'] = Hash::make($validatedAttributes['password']);
        $user = User::create($validatedAttributes);

        // Triggers SendEmailVerificationNotification because User implements MustVerifyEmail.
        event(new Registered($user));

        return response(status: 201);
    }

}
