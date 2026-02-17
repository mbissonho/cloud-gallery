<?php

namespace Tests\Feature;

use App\Models\Image;
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
}
