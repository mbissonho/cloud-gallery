<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppHttpTest extends TestCase
{
    public function test_non_json_get_request_to_unknown_path_redirects_to_root(): void
    {
        $this->get('/anything-else', ['Accept' => 'text/html'])
            ->assertRedirect('/');
    }
}
