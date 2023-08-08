<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $response = $this->get('/api/v2/webhook?view=channel&id=1e4b926d-54d7-4932-b8a6-7cdc65abd992');

        $response->assertStatus(200);
    }
}
