<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InviteTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $response = $this->withHeaders([
            'Authorization' => env('TEST_USER_TOKEN'),
        ])->get('/api/v2/invite?view=studio&studio=visuddhinanda');

        $response->assertOk();
    }
}
