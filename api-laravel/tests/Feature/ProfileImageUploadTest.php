<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileImageUploadTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_get_profile_image_upload_url(): void
    {
        $this->getJson(route('api.v1.profile.s3_pre_signed_url', [
            'filename'     => 'photo.jpg',
            'content_type' => 'image/jpeg',
        ]))->assertUnauthorized();
    }

    public function test_authenticated_user_cannot_upload_invalid_file_type(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('api.v1.profile.s3_pre_signed_url', [
                'filename'     => 'photo.gif',
                'content_type' => 'image/gif',
            ]))->assertStatus(400);
    }

    public function test_authenticated_user_can_get_profile_image_upload_url(): void
    {
        //Arrange
        $temporaryUrl = 'https://temporary-url.test';
        Storage::fake('profile-image');

        $fakeFilesystem = Storage::disk('profile-image');
        $proxyMockedFakeFilesystem = \Mockery::mock($fakeFilesystem);
        $proxyMockedFakeFilesystem->shouldReceive('temporaryUploadUrl')
            ->once()
            ->andReturn(['url' => $temporaryUrl, 'headers' => []]);

        Storage::set('profile-image', $proxyMockedFakeFilesystem);

        //Act and Assert
        $this->actingAs($this->user)
            ->getJson(route('api.v1.profile.s3_pre_signed_url', [
                'filename'     => 'photo.jpg',
                'content_type' => 'image/jpeg',
            ]))
            ->assertOk()
            ->assertJson(['url' => $temporaryUrl]);

        $this->assertNotNull($this->user->fresh()->new_photo_storage_hash);
    }
}