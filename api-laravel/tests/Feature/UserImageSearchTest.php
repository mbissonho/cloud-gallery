<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Tests\TestCase;

class UserImageSearchTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_search_own_images(): void
    {
        $this->getJson(route('api.v1.image.user-search'))
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_search_own_images(): void
    {
        //Arrange
        Image::factory()
            ->available()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this->actingAs($this->user)
            ->getJson(route('api.v1.image.user-search'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_only_sees_own_images(): void
    {
        //Arrange
        Image::factory()->available()->ofUser($this->user->id)->create();
        Image::factory()->available()->ofUser(User::factory()->create()->id)->create();

        //Act and Assert
        $this->actingAs($this->user)
            ->getJson(route('api.v1.image.user-search'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_image_search_includes_all_statuses(): void
    {
        //Arrange — owner should see their own processing and disabled images too
        Image::factory()->available()->ofUser($this->user->id)->create();
        Image::factory()->processing()->ofUser($this->user->id)->create();
        Image::factory()->disabled()->ofUser($this->user->id)->create();

        //Act and Assert
        $this->actingAs($this->user)
            ->getJson(route('api.v1.image.user-search'))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}