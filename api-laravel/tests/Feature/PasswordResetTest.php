<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\QueuedResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset throttle:auth state between tests (the limiter is keyed by IP
        // and persists in the cache across requests).
        Cache::flush();
    }

    public function test_forgot_password_sends_reset_link_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route('api.v1.auth.forgot-password'), [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertSentTo($user, QueuedResetPassword::class);
    }

    public function test_forgot_password_returns_ok_for_unknown_email_without_sending(): void
    {
        Notification::fake();

        $this->postJson(route('api.v1.auth.forgot-password'), [
            'email' => 'no-one@mail.com',
        ])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_forgot_password_requires_a_valid_email(): void
    {
        $this->postJson(route('api.v1.auth.forgot-password'), [
            'email' => 'not-an-email',
        ])->assertUnprocessable();
    }

    public function test_reset_password_changes_user_password_with_valid_token(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson(route('api.v1.auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk();

        Event::assertDispatched(PasswordReset::class);
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('original')]);

        $this->postJson(route('api.v1.auth.reset-password'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertUnprocessable();

        $this->assertTrue(Hash::check('original', $user->fresh()->password));
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson(route('api.v1.auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ])->assertUnprocessable();
    }

    public function test_reset_password_rejects_password_shorter_than_minimum(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson(route('api.v1.auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short12',
            'password_confirmation' => 'short12',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors('password');
    }

    public function test_reset_password_rejects_password_exceeding_max_length(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);
        $tooLong = str_repeat('a', 51);

        $this->postJson(route('api.v1.auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $tooLong,
            'password_confirmation' => $tooLong,
        ])->assertUnprocessable()
          ->assertJsonValidationErrors('password');
    }

    public function test_forgot_password_is_blocked_after_exceeding_rate_limit(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // throttle:auth allows 10 req/min — exhaust the limit
        for ($i = 0; $i < 10; $i++) {
            $this->postJson(route('api.v1.auth.forgot-password'), [
                'email' => $user->email,
            ]);
        }

        $this->postJson(route('api.v1.auth.forgot-password'), [
            'email' => $user->email,
        ])->assertTooManyRequests();
    }
}
