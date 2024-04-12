<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DiscussionCountTest extends TestCase
{
    private $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2OTc3Mjg2ODUsImV4cCI6MTcyOTI2NDY4NSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOjR9.fiXhnY2LczZ9kKVHV0FfD3AJPZt-uqM5wrDe4EhToVexdd007ebPFYssZefmchfL0mx9nF0rgHSqjNhx4P0yDA';

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_store()
    {
        //testing store
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('POST', '/api/v2/discussion-count',
                    [
                        'course_id'=>'b3145a94-e400-459d-9507-acbb05256e4e',
                        'sentences'=>[[168,916,2,9]],
                    ]);

        $response->assertOk();
    }
}
