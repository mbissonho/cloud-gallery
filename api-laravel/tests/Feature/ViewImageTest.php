<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Tests\TestCase;

class ViewImageTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_processing_image_cannot_be_publicly_found(): void
    {
        //Arrange
        $processingImage = Image::factory()
            ->processing()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this->getJson(route('api.v1.image.details', ['imageId' => $processingImage->id ]))
            ->assertNotFound()
            ->assertJson([
                'message' => 'Not found'
            ]);
    }

    public function test_unauthenticated_user_can_get_publicly_image_details()
    {
        //Arrange
        $image = Image::factory()
            ->available()
            ->ofUser(User::factory()->create()->id)
            ->create();

        $image->turnAvailable();

        //Act and Assert
        $this
            ->getJson(route('api.v1.image.details', ['imageId' => $image->id ]))
            ->assertOk();
    }

    public function test_unauthenticated_user_cannot_get_disabled_image_details()
    {
        //Arrange
        $disabledImage = Image::factory()
            ->disabled()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this
            ->getJson(route('api.v1.image.details', ['imageId' => $disabledImage->id ]))
            ->assertNotFound();
    }

    public function test_authenticated_user_can_get_disabled_image_details_of_your_property()
    {
        //Arrange
        $disabledImage = Image::factory()
            ->disabled()
            ->ofUser($this->user->id)
            ->create();

        //Act and Assert
        $this
            ->actingAs($this->user)
            ->getJson(route('api.v1.image.details', ['imageId' => $disabledImage->id ]))
            ->assertOk();
    }
}
