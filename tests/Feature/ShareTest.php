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
    public function test_example()
    {
        $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2NjgyMzE3MTksImV4cCI6MTY5OTc2NzcxOSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOiI0In0.LV4ItC5VCqXpbKIXT1zePcnfi-heCf3Df63w7qbXsT1i5KJtwJJC938CLgANjqwcQFa3lrR5TqvT1kkqD-Mmgg';
        $response = $this->withHeaders([
            'Authorization' => $token,
        ])->get('/api/v2/share?view=group&id=8079d293-5057-449f-b8b2-6482531d2434');

        $response->assertOk();
    }
}
