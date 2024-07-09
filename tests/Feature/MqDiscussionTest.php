<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MqDiscussionTest extends TestCase
{
    protected $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2NjgyMzE3MTksImV4cCI6MTY5OTc2NzcxOSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOiI0In0.LV4ItC5VCqXpbKIXT1zePcnfi-heCf3Df63w7qbXsT1i5KJtwJJC938CLgANjqwcQFa3lrR5TqvT1kkqD-Mmgg';

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store()
    {
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('POST', '/api/v2/discussion',
                    [
                        'title'=>'test'.time(),
                        'res_id'=>'1aebd3c0-61cc-4e5b-a56e-b98ffb7f1430',
                        'res_type'=>'sentence',
                    ]);

        $response->assertOk();
    }
}
