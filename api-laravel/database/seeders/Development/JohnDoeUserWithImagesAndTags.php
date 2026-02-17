<?php

namespace Database\Seeders\Development;

use App\Models\Image;
use App\Models\ImageStatus;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class JohnDoeUserWithImagesAndTags extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imagesData = [
            [
                'storage_key' => '51ff8875-58cd-4554-b690-86dcd714ccfb',
                'title' => 'Scarlet Macaw',
                'description' => 'A vibrant scarlet macaw spreads its colorful wings mid-flight in a natural setting, captured in stunning 4K Ultra HD for a vivid PC desktop wallpaper.',
                'tags' => [
                    'Animal',
                    'Scarlet Macaw'
                ]
            ],
            [
                'storage_key' => '9f1cb324-15f1-4967-8343-db6fd70d281b',
                'title' => 'Medellin',
                'description' => 'Nighttime cityscape of Medellin, Colombia, showcasing illuminated buildings and urban lights in a vibrant 4K Ultra HD view.',
                'tags' => [
                    'Building',
                    'Cityscape',
                    'Night',
                    'Colombia',
                    'Medellin',
                    'Man Made',
                    'City'
                ]
            ],
            [
                'storage_key' => '7726e282-8f06-4808-900c-ace19841c079',
                'title' => 'Berlin',
                'description' => 'Wallpaper showcasing Berlin\'s cityscape with iconic man-made structures, including the Fernsehturm tower, framed by a tree-lined avenue under a clear sky.',
                'tags' => [
                    'Building',
                    'Cityscape',
                    'Berlin',
                    'Man Made',
                    'City'
                ]
            ],
            [
                'storage_key' => '752625bd-1fd7-44cf-a460-b1fde9f979f5',
                'title' => 'Cute Cat',
                'description' => 'A curious orange cat staring amid vibrant pink and white summer flowers in a lush garden, captured in stunning 4K Ultra HD.',
                'tags' => [
                    'Stare',
                    'Summer',
                    'White Flower',
                    'Pink Flower',
                    'Animal',
                    'Cat'
                ]
            ],
            [
                'storage_key' => '48850855-0078-4d6f-8e80-b2c3ee941db4',
                'title' => 'Canadian Bridge',
                'description' => 'A man-made bridge in Canada illuminated with blue lights at night, reflecting on the calm water, captured in stunning 4K Ultra HD detail.',
                'tags' => [
                    'Light',
                    'Blue',
                    'Canada',
                    'Night',
                    'Reflection',
                    'Man Made',
                    'Bridge'
                ]
            ],
            [
                'storage_key' => '0b2dd490-bd88-4596-9347-d6c4ed3a47b5',
                'title' => 'Toyama Castle',
                'description' => 'Toyama Castle in Japan at twilight, beautifully reflected in the calm pond, captured in a stunning 4K Ultra HD desktop wallpaper.',
                'tags' => [
                    'Twilight',
                    'Pond',
                    'Castle',
                    'Reflection',
                    'Japan',
                    'Man Made',
                    'Toyama Castle'
                ]
            ],
            [
                'storage_key' => '291d332e-ca58-4d22-8fbd-d4c23cfa3d4f',
                'title' => 'Eltz Castle',
                'description' => 'A 4K Ultra HD view of the man-made Eltz Castle surrounded by lush forest and mist under a cloudy sky.',
                'tags' => [
                    'Castle',
                    'Eltz Castle',
                ]
            ],
            [
                'storage_key' => 'bb8508f7-0efb-47ec-a448-87840b31963f',
                'title' => 'Tiger',
                'description' => '4K Ultra HD PC desktop wallpaper showing a tiger partially submerged in green water, surrounded by natural foliage, highlighting the animal\'s striking colors and patterns.',
                'tags' => [
                    'Animal',
                    'Tiger'
                ]
            ]
        ];


        $tags = (new Collection($imagesData))->pluck('tags')->flatten()->all();

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@mail.com',
            'password' => 'password',
            'bio' => 'Professional landscape photographer specializing in natural light.',
            'photo_storage_key' => '56330724-7ac8-4bf9-bbd0-0303179cd60e.jpg'
        ]);

        $createdTagsIds = [];
        foreach ($tags as $tagName) {
            if(isset($createdTagsIds[$tagName])) continue;

            $createdTagsIds[$tagName] = Tag::factory()->create([
                'name' => $tagName,
                'user_id' => $user->id
            ])->id;
        }

        foreach ($imagesData as $imageData) {
            $image = Image::factory()->create([
                'title' => $imageData['title'],
                'description' => $imageData['description'],
                'user_id' => $user->id,
                'status' => ImageStatus::AVAILABLE,
                'storage_key' => sprintf("%s.jpg", $imageData['storage_key']),
                'storage_bucket' => config('cloudgallery.main-image-bucket'),
                'thumbnail_storage_bucket' => config('cloudgallery.thumbnail-image-bucket')
            ]);

            $tagIdsForImage = [];
            foreach ($imageData['tags'] as $tagName) {
                $tagIdsForImage[] = $createdTagsIds[$tagName];
            }

            $image->tags()->attach($tagIdsForImage);
            $image->save();
            //TODO: Move things to factory later
        }

    }
}
