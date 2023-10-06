<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransferTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->withHeaders([
            'Authorization' => env('TEST_USER_TOKEN'),
        ])->get('/api/v2/transfer?view=studio&name=visuddinanda');


        $response->assertStatus(200);
    }
}
