<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected array $userData = [
        'name' => 'John Doe',
        'email' => 'johndoe@mail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    public function test_user_can_login_and_receive_a_token_by_passing_valid_credentials()
    {
        //Arrange
        $result = array_merge(
            Arr::except($this->userData, 'password_confirmation'),
            [
                'password' => Hash::make($this->userData['password'])
            ]
        );

        $user = User::factory()->create($result);

        //Act and assert
        $this->postJson(
            route('api.v1.auth.login'),
            Arr::only($this->userData, ['email', 'password'])
        )
        ->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ])
        ->assertSee('token');
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        //Act and assert
        $this->postJson(
            route('api.v1.auth.login'),
            ['email' => 'invalid@mail.com', 'password' => 'wrong']
        )
            ->assertUnauthorized();
    }
}
