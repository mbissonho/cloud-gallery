<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserImageSearchResource extends ImageSearchResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'status' => $this->resource->status
            ]
        );
    }
}
