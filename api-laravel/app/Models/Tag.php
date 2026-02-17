<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, Searchable, ProcessSearchBody;

    protected $fillable = ['name'];

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class);
    }

    public function searchableAs(): string
    {
        return 'tags_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }

    function processBody(Builder $builder, array $body, array $options = []): array
    {
        $body['query'] = [
            'multi_match' => [
                'query' => $builder->query,
                'type' => 'bool_prefix',
                'fields' => [
                    'name',
                    'name._2gram',
                    'name._3gram'
                ]
            ]
        ];

        return $body;
    }
}
