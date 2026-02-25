<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageDetailsResource;
use App\Models\Image;
use App\Models\ImageStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageDetailsController extends Controller
{
    public function __invoke(Request $request, $imageId): ImageDetailsResource
    {
        $user = Auth::guard('sanctum')->user();

        $image = Image::with(['user:name,bio,photo_storage_key,id', 'tags:name,id'])
            ->where(function ($query) use ($user) {
                $query->where('status', ImageStatus::AVAILABLE);

                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->findOrFail($imageId);

        return new ImageDetailsResource($image);
    }
}
