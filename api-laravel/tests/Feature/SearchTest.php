<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class SearchTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_not_available_image_is_not_publicly_searchable()
    {
        //Arrange
        Image::factory()
            ->processing()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this->getJson(route('api.v1.image.search'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_available_image_is_publicly_searchable(): void
    {
        //Arrange
        Image::factory()
            ->available()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this->getJson(route('api.v1.image.search'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_available_image_search_can_filtered_by_tag(): void
    {
        //Arrange
        $tag = Tag::factory()->create([
            'name' => 'landscape',
            'user_id' => $this->user->id,
        ]);

        Image::factory()
            ->available()
            ->ofUser(User::factory()->create()->id)
            ->create();

        Image::factory()
            ->count(2)
            ->available()
            ->ofUser($this->user->id)
            ->create();

        $taggedImage = Image::factory()
            ->available()
            ->ofUser($this->user->id)
            ->create();

        $taggedImage->tags()->attach($tag->id);
        $taggedImage->save(); // re-index with tag_ids

        //Act and Assert
        $this->getJson(route('api.v1.image.search'))
            ->assertOk()
            ->assertJsonCount(4, 'data');

        $this->getJson(route('api.v1.image.search', ['tag_id' => $tag->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
