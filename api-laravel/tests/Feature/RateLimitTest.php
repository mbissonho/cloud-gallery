<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiter state between tests
        Cache::flush();
    }

    public function test_auth_routes_include_rate_limit_headers(): void
    {
        $this->postJson(route('api.v1.auth.login'), [
            'email'    => 'x@x.com',
            'password' => 'wrong',
        ])->assertHeader('X-RateLimit-Limit')
          ->assertHeader('X-RateLimit-Remaining');
    }

    public function test_public_routes_include_rate_limit_headers(): void
    {
        $this->getJson(route('api.v1.image.search'))
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit')
            ->assertHeader('X-RateLimit-Remaining');
    }

    public function test_auth_endpoint_is_blocked_after_exceeding_limit(): void
    {
        // throttle:auth allows 10 req/min — exhaust the limit
        for ($i = 0; $i < 10; $i++) {
            $this->postJson(route('api.v1.auth.login'), [
                'email'    => 'x@x.com',
                'password' => 'wrong',
            ]);
        }

        // 11th request must be rejected
        $this->postJson(route('api.v1.auth.login'), [
            'email'    => 'x@x.com',
            'password' => 'wrong',
        ])->assertTooManyRequests();
    }
}