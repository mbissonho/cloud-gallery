<?php

namespace App\Models;

use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory, Searchable, ProcessSearchBody;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail_storage_bucket',
        'storage_bucket',
        'storage_key',
        'status'
    ];

    public function turnAvailable()
    {
        $this->status = ImageStatus::AVAILABLE;
        $this->save();
    }

    public function delete(): ?bool
    {
        Storage::disk('main-image')->delete($this->storage_key);
        Storage::disk('thumbnail-image')->delete($this->storage_key);

        return parent::delete();
    }

    public function getThumbnailUrl(): ?string
    {
        return Storage::disk('thumbnail-image')->url('thumbnail/' . $this->storage_key ?? null);
    }

    public function getMainImageUrl(): ?string
    {
        return Storage::disk('main-image')->url($this->storage_key ?? null);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    public function searchableAs(): string
    {
        return 'images_index';
    }

    public function toSearchableArray(): array
    {
        $this->load('user', 'tags');

        return [
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'storage_key' => $this->storage_key,
            'status' => is_string($this->status) ? $this->status : $this->status->value,
            'tag_ids' => $this->tags->pluck('id')->all(),
            'tag_names' => $this->tags->pluck('name')->all(),
            'created_at' => $this->created_at->timestamp
        ];
    }

    protected function makeAllSearchableUsing(EloquentBuilder $query): EloquentBuilder
    {
        return $query
            ->with('user')
            ->with('tags');
    }

    protected static function booted()
    {
        static::saving(function ($image){
            if($image->status === null) {
                $image->status = ImageStatus::PROCESSING;
                $image->storage_bucket = config('cloudgallery.main-image-bucket');
                $image->thumbnail_storage_bucket = config('cloudgallery.thumbnail-image-bucket');
            }
        });
    }

    function processBody(Builder $builder, array $body, array $options = []): array
    {
        if(!empty($builder->query)) {
            $body['query']['bool']['must'] = [
                'match' => [
                    'title' => $builder->query
                ]
            ];
        }

        if(!empty($builder->orders)) {
            $sortByColumn = $builder->orders[0];
            $body['sort'] = [
                [
                    $sortByColumn['column'] => [
                        'order' => $sortByColumn['direction']
                    ]
                ]
            ];
        }

        return $body;
    }
}
