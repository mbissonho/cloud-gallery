<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditUserProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserProfileEditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EditUserProfileRequest $request)
    {
        $user = $request->user();

        $validated = $request->validated();

        $user->name = $validated['name'];

        if ($request->has('bio')) {
            $user->bio = $validated['bio'];
        }

        $message = trans('user.profile.update.success');
        if ($request->filled('new_password')) {
            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => [trans('auth.failed')]
                ]);
            }

            $user->password = Hash::make($validated['new_password']);
            $message = trans('user.profile.update.successWithPassword');
        }

        $user->save();

        return response()->json([
            'message' => $message,
        ], 200);
    }
}
