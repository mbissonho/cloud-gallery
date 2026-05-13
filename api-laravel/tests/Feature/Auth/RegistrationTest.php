<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use ReflectionMethod;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_register_endpoint_creates_user_and_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Secret-123',
            'password_confirmation' => 'Secret-123',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        Event::assertDispatched(
            Registered::class,
            fn (Registered $event) => $event->user->email === 'test@example.com',
        );
    }

    public function test_register_route_is_listed_in_csrf_except_paths(): void
    {
        // The CSRF middleware auto-skips inside tests (runningUnitTests check),
        // so a plain POST without a token would pass regardless. Inspect the
        // configured `except` list directly to prove the exemption set in
        // bootstrap/app.php actually reached the middleware — and that other
        // stateful routes (e.g. /login) are still being protected.
        $middleware = $this->app->make(ValidateCsrfToken::class);

        $inExceptArray = new ReflectionMethod($middleware, 'inExceptArray');
        $inExceptArray->setAccessible(true);

        $register = Request::create('/api/v1/auth/register', 'POST');
        $login = Request::create('/login', 'POST');

        $this->assertTrue(
            $inExceptArray->invoke($middleware, $register),
            'POST /api/v1/auth/register must be in the CSRF except list.',
        );
        $this->assertFalse(
            $inExceptArray->invoke($middleware, $login),
            'POST /login must still validate CSRF — only register is exempt.',
        );
    }
}
