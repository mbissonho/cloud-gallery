<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'description' => $this->resource->description,
            'created_at' => $this->resource->created_at,
            'author_name' => $this->resource->user->name,
            'author_bio' => $this->resource->user->bio,
            'author_photo' => $this->resource->user->getProfilePhotoUrl()
        ];
    }
}
