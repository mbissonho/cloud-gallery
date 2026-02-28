<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;

class TagSearchTest extends TestCase
{

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_tag_search_is_publicly_accessible(): void
    {
        $this->getJson(route('api.v1.tag.search'))
            ->assertOk();
    }

    public function test_can_list_all_tags(): void
    {
        //Arrange
        Tag::factory()->create(
            [
                'name' => 'landscape',
                'user_id' => $this->user->id,
            ]
        );
        Tag::factory()->create(
            [
                'name' => 'portrait',
                'user_id' => $this->user->id,
            ]
        );

        //Act and Assert
        $this->getJson(route('api.v1.tag.search'))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_tag_search_returns_paginated_response(): void
    {
        //Arrange
        Tag::factory()->create(
            [
                'name' => 'landscape',
                'user_id' => $this->user->id,
            ]
        );

        //Act and Assert
        $this->getJson(route('api.v1.tag.search'))
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }
}
