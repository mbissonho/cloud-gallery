<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

    /**
     * Always returns 200 regardless of whether the email is registered, so the
     * endpoint cannot be used to probe which addresses exist in the database.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        Password::broker()->sendResetLink($request->validated());

        return response()->json(['message' => trans('passwords.sent')]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::broker()->reset(
            $request->validated(),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => trans($status)], 422);
        }

        return response()->json(['message' => trans($status)]);
    }

}
