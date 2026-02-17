<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __invoke(Request $request, $userId): UserProfileResource
    {
        return new UserProfileResource(
            User::with('latestImage')
                ->withCount('publishedImages as published_images_count')
                ->findOrFail($userId)
        );
    }
}
