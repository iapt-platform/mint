<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SentHistoryTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {

        $response = $this->get('/api/v2/sent_history?view=sentence&id=998bed0c-883e-4384-8982-ef0bfa601e58');

        $response->assertStatus(200);
    }
}
