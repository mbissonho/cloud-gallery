<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Get session logged in user information
     */
    public function user(Request $request): UserProfileResource
    {
        $userId = $request->user()->id;

        $cacheKey = "user_profile_{$userId}";

        $user = Cache::flexible($cacheKey, [300, 600], function () use ($userId) {
            return User::with('latestImage')
                ->withCount('publishedImages as published_images_count')
                ->findOrFail($userId);
        });

        return new UserProfileResource($user);
    }

}
