<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'thumbnail_url' => $this->resource->getThumbnailUrl(),
            'created_at' => $this->resource->created_at->format('m/d/Y H:i'),
            'tag_ids' => $this->resource->tag_ids,
            'tag_names' => $this->resource->tag_names
        ];
    }
}
