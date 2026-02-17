<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\ImageStatus;
use App\Models\User;
use Illuminate\Support\Arr;
use Tests\TestCase;

class EditImageTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_edit_its_available_image(): void
    {
        //Arrange
        $availableImage = Image::factory()
            ->available()
            ->ofUser($this->user->id)
            ->create();

        $newImageData = [
            'tag_ids' => [],
            'description' => 'New description',
            'status' => ImageStatus::DISABLED->value
        ];

        //Act
        $this->actingAs($this->user)
            ->putJson(
                route('api.v1.image.edit', ['image' => $availableImage->id ]),
                $newImageData
            )
            ->assertOk();

        //Assert
        $this->assertDatabaseHas('images', Arr::except($newImageData, ['tag_ids']));
    }
}
