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

        $query = Image::with(['user:name,bio,photo_storage_key,id', 'tags:name,id'])
            ->where('status', ImageStatus::AVAILABLE);

        if(null !== $user) {
            $query
                ->orWhere('user_id', $user?->id ?? null);
        }

        return new ImageDetailsResource($query->findOrFail($imageId));
    }
}
