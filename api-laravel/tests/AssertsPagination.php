<?php

namespace Tests;

use Illuminate\Testing\TestResponse;

trait AssertsPagination
{
    protected function assertSeePaginationJsonProperties(TestResponse $response): void
    {
        $response
            ->assertSee(['links', 'meta']);
    }
}
