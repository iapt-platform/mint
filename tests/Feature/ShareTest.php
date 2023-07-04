<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShareTest extends TestCase
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
        ])->get('/api/v2/share?view=group&id=8079d293-5057-449f-b8b2-6482531d2434');

        $response->assertOk();
        $response = $this->withHeaders([
            'Authorization' => env('TEST_USER_TOKEN'),
        ])->get('/api/v2/share?view=res&id=0b750ac3-771e-4346-994c-1d87ea6d68a0');

        $response->assertOk();
    }
}
