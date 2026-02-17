<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    public function test_authenticated_user_can_edit_your_profile_data(): void
    {
        //Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'bio' => 'A simple user that like photographs.'
        ]);

        $newUserData = [
            'name' => 'John Doe Jr',
            'bio' => 'An experienced photographer'
        ];

        //Act and assert
        $this
            ->actingAs($user)
            ->putJson(
                route('api.v1.profile.edit'),
                $newUserData
            )
            ->assertOk()
            ->assertJson([
                'message' => trans('user.profile.update.success')
            ]);

        $this->assertDatabaseHas('users', $newUserData);
    }

    public function test_unauthenticated_cannot_perform_profile_or_password_update()
    {
        //Arrange
        User::factory()->create([
            'name' => 'John Doe',
            'bio' => 'A simple user that like photographs.'
        ]);

        $newUserData = [
            'name' => 'John Doe Jr',
            'bio' => 'An experienced photographer'
        ];

        //Act and assert
        $this
            ->putJson(
                route('api.v1.profile.edit'),
                $newUserData
            )
            ->assertUnauthorized();
    }

    public function test_authenticated_can_change_your_password()
    {
        //Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'bio' => 'A simple user that like photographs.',
            'password' => Hash::make('12345678')
        ]);

        $newUserData = [
            'name' => 'John Doe',
            'bio' => 'A simple user that like photographs.',
            'password' => '12345678',
            'new_password' => '12345679'
        ];

        //Act and assert
        $this
            ->actingAs($user)
            ->putJson(
                route('api.v1.profile.edit'),
                $newUserData
            )
            ->assertOk()
            ->assertJson([
                'message' => trans('user.profile.update.successWithPassword')
            ]);

        $this->assertDatabaseHas(
            'users',
            Arr::except($newUserData, ['password', 'new_password']),
        );

        Hash::check(Hash::make($newUserData['new_password']), $user->fresh()->password);
    }

    public function test_authenticated_must_send_correctly_your_current_password_to_perform_a_password_change()
    {
        //Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'bio' => 'A simple user that like photographs.',
            'password' => Hash::make('12345678')
        ]);

        $newUserData = [
            'name' => 'John Doe',
            'bio' => 'A simple user that like photographs.',
            'password' => 'wrong-password',
            'new_password' => '12345679'
        ];

        //Act and assert
        $this
            ->actingAs($user)
            ->putJson(
                route('api.v1.profile.edit'),
                $newUserData
            )
            ->assertUnprocessable()
            ->assertJson([
                'message' => trans('auth.failed')
            ]);
    }

    public function test_unauthenticated_user_can_see_profile_information_of_any_user()
    {
        //Arrange
        $user = User::factory()->create();

        $image = Image::factory()
            ->available()
            ->ofUser($user->id)
            ->create([
                'title' => 'Image Title'
            ]);

        $image->turnAvailable();

        //Act and Assert
        $this
            ->getJson(route('api.v1.profile.details', ['userId' => $user->id ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'bio' => $user->bio,
                    'published_images_count' => 1,
                    'last_published_image' => [
                        'title' => 'Image Title'
                    ]
                ]
            ]);
    }

}
