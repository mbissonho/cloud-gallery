<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Tests\TestCase;

class DeleteImageTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_delete_its_available_image(): void
    {
        //Arrange
        $availableImage = Image::factory()
            ->available()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this->actingAs($this->user)
            ->deleteJson(route('api.v1.image.delete', ['image' => $availableImage->id ]))
            ->assertNoContent();
    }

    public function test_authenticated_user_cannot_delete_image_of_another_user(): void
    {
        //Arrange
        $availableImage = Image::factory()
            ->available()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this->actingAs(User::factory()->create())
            ->deleteJson(route('api.v1.image.delete', ['image' => $availableImage->id ]))
            ->assertNotFound()
            ->assertJson([
                'message' => 'Not found'
            ]);
    }

    public function test_404_json_for_authenticated_user_while_trying_delete_not_found_image(): void
    {
        //Act and Assert
        $this->actingAs($this->user)
            ->deleteJson(route('api.v1.image.delete', ['image' => 444 ]))
            ->assertNotFound()
            ->assertJson([
                'message' => 'Not found'
            ]);
    }
}
