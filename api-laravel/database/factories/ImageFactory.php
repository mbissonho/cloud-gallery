<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\ImageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'description' => fake()->text(300),
            'status' => ImageStatus::AVAILABLE,
            'storage_key' => sprintf("%s.jpg", Uuid::getFactory()->uuid4()->toString()),
            'storage_bucket' => config('cloudgallery.main-image-bucket'),
            'thumbnail_storage_bucket' => config('cloudgallery.thumbnail-image-bucket')
        ];
    }

    public function processing(): self
    {
        return $this->state(function () {
            return [
                'status' => ImageStatus::PROCESSING
            ];
        });
    }

    public function disabled(): self
    {
        return $this->state(function () {
            return [
                'status' => ImageStatus::DISABLED
            ];
        });
    }

    public function available(): self
    {
        return $this->state(function () {
            return [
                'status' => ImageStatus::AVAILABLE
            ];
        });
    }

    public function ofUser($userId): self
    {
        return $this->state(function () use ($userId){
            return [
                'user_id' => $userId
            ];
        });
    }
}
