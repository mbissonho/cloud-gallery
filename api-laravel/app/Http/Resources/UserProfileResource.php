<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'bio' => $this->resource->bio,
            'user_since' => $this->resource->created_at->diffForHumans(),
            'profile_photo_key' => $this->resource->getProfilePhotoKey(),
            'profile_photo_url' => $this->resource->getProfilePhotoUrl(),
            'published_images_count' => $this->resource->published_images_count,
            'last_published_image' => new UserProfileImageResource($this->resource->latestImage) ?? null
        ];
    }
}
