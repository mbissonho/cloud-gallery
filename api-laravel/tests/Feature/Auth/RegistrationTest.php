<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $response = $this->post(route('api.v1.auth.register'), $data);

        $response
            ->assertCreated();

        $this->assertDatabaseHas(
            'users',
            Arr::except($data, ['password', 'password_confirmation'])
        );
    }
}
