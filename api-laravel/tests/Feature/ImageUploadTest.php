<?php

namespace Tests\Feature;

use App\Jobs\HandleThumbnailUpload;
use App\Models\Image;
use App\Models\ImageStatus;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Sti3bas\ScoutArray\Facades\Search;
use Tests\TestCase;
use Tests\Util\Http;

class ImageUploadTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_get_a_s3_pre_signed_url_to_perform_upload(): void
    {
        $this
            ->getJson(route('api.v1.image.s3_pre_signed_url', [
                'filename' => 'amazing-field.png', 'content_type' => 'image/png']))
            ->assertStatus(401);
    }

    public function test_authenticated_user_cannot_get_a_s3_pre_signed_url_for_a_invalid_filename(): void
    {
        //Act and Assert
        $params = [
            'filename' => 'invalid-file-name-1',
            'content_type' => 'image/png',
            'file_title' => 'Amazing Field'
        ];

        $url = route('api.v1.image.s3_pre_signed_url') . '?'
            . Http::buildQuery($params, asFlatArray: ['file_tag_ids']);

        $this
            ->actingAs($this->user)
            ->getJson($url)
            ->assertStatus(400);
    }

    public function test_authenticated_user_can_get_a_s3_pre_signed_url_to_perform_upload(): void
    {
        //Arrange
        $temporaryUrl = 'https://temporary-url.test';
        $fileTitle = 'Amazing Field';
        Storage::fake('main-image');

        $fakeFilesystem = Storage::disk('main-image');
        $proxyMockedFakeFilesystem = \Mockery::mock($fakeFilesystem);
        $proxyMockedFakeFilesystem->shouldReceive('temporaryUploadUrl')
            ->once()
            ->andReturn([
                'url' => $temporaryUrl,
                'headers' => []
            ]);

        Storage::set('main-image', $proxyMockedFakeFilesystem);

        $createdTagsIds = [];
        foreach (['fields', 'landscape'] as $tagName) {
            $createdTagsIds[] = Tag::factory()->create([
                'name' => $tagName,
                'user_id' => $this->user->id
            ])->id;
        }

        //Act and Assert
        $params = [
            'filename' => 'amazing-field.png',
            'content_type' => 'image/png',
            'file_title' => 'Amazing Field',
            'file_description' => '',
            'file_tag_ids' => [2, 1]
        ];

        $url = route('api.v1.image.s3_pre_signed_url') . '?'
            . Http::buildQuery($params, asFlatArray: ['file_tag_ids']);

        $this
            ->actingAs($this->user)
            ->getJson($url)
            ->assertStatus(200)
            ->assertJson(['url' => $temporaryUrl]);

        //Asserting database
        $this->assertDatabaseHas('images', [
            'title' => $fileTitle,
            'status' => ImageStatus::PROCESSING->value,
            'storage_bucket' => config('cloudgallery.main-image-bucket'),
            'thumbnail_storage_bucket' => config('cloudgallery.thumbnail-image-bucket'),
        ]);

        $image = Image::first();

        $this->assertDatabaseHas('image_tag', [
            'image_id' => $image->id,
            'tag_id' => $createdTagsIds[0]
        ]);

        $this->assertDatabaseHas('image_tag', [
            'image_id' => $image->id,
            'tag_id' => $createdTagsIds[1]
        ]);

        $this->assertNotNull($image->storage_key, 'Image storage key is null');
        $uuid = explode('.', $image->storage_key)[0] ?? null;
        $this->assertTrue(Uuid::isValid($uuid), 'Image storage key is not valid');

        Search::assertNotEmptyIn('images_index');
    }

    public function test_image_is_set_as_available_on_search_engine_after_thumbnail_is_ready()
    {
        $userId = $this->user->id;
        $storageKey = '512fb0da-6cd3-4fcc-b6c6-5bc67ee2a54e.jpeg';
        $image = Image::create([
            'user_id' => $userId,
            'title' => 'Nice photo',
            'storage_key' => $storageKey
        ]);

        $data = [
            'bucket' => 'thumbnail-bucket',
            'key' => $storageKey
        ];

        Search::assertNotEmptyIn('images_index');
        Search::assertContains($image)
            ->assertContains($image, function ($record) use ($userId) {
                return $record['status'] === ImageStatus::PROCESSING->value &&
                    $record['user_id'] === $userId;
            });

        (new HandleThumbnailUpload($data))->handle();

        Search::assertNotEmptyIn('images_index');
        Search::assertContains($image)
        ->assertContains($image, function ($record) use ($userId) {
            return $record['status'] === ImageStatus::AVAILABLE->value &&
                $record['user_id'] === $userId;
        });
    }

    public function test_user_will_reuse_processing_image_with_same_name(): void
    {
        //Arrange
        $temporaryUrl = 'https://temporary-url.test';
        $fileTitle = 'Amazing Field';
        Storage::fake('main-image');

        $fakeFilesystem = Storage::disk('main-image');
        $proxyMockedFakeFilesystem = \Mockery::mock($fakeFilesystem);
        $proxyMockedFakeFilesystem->shouldReceive('temporaryUploadUrl')
            ->times(2)
            ->andReturn([
                'url' => $temporaryUrl,
                'headers' => []
            ]);

        Storage::set('main-image', $proxyMockedFakeFilesystem);

        $createdTagsIds = [];
        foreach (['fields', 'landscape'] as $tagName) {
            $createdTagsIds[] = Tag::factory()->create([
                'name' => $tagName,
                'user_id' => $this->user->id
            ])->id;
        }

        //Act and Assert
        $params = [
            'filename' => 'amazing-field.png',
            'content_type' => 'image/png',
            'file_title' => 'Amazing Field',
            'file_description' => '',
            'file_tag_ids' => $createdTagsIds
        ];

        $url = route('api.v1.image.s3_pre_signed_url') . '?'
            . Http::buildQuery($params, asFlatArray: ['file_tag_ids']);

        $this
            ->actingAs($this->user)
            ->getJson($url)
            ->assertStatus(200)
            ->assertJson(['url' => $temporaryUrl]);


        $this
            ->actingAs($this->user)
            ->getJson($url)
            ->assertStatus(200)
            ->assertJson(['url' => $temporaryUrl]);

        $this->assertDatabaseHas('images', [
            'title' => $fileTitle
        ]);

        $this->assertDatabaseCount('images', 1);
    }

}
