<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class DiscussionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2NjgyMzE3MTksImV4cCI6MTY5OTc2NzcxOSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOiI0In0.LV4ItC5VCqXpbKIXT1zePcnfi-heCf3Df63w7qbXsT1i5KJtwJJC938CLgANjqwcQFa3lrR5TqvT1kkqD-Mmgg';
        //testing index
        $response = $this->get('/api/v2/discussion?view=question&id=eae9fd6f-7bac-4940-b80d-ad6cd6f433bf');

        $response->assertStatus(200);

        //testing store
        $response = $this->withHeaders([
            'Authorization' => $token,
        ])->json('POST', '/api/v2/discussion',
                    [
                        'title'=>'test',
                        'res_id'=>'eae9fd6f-7bac-4940-b80d-ad6cd6f433bf',
                        'res_type'=>'wbw',
                    ]);

        $response->assertOk();

        $id = $response['data']['id'];
        $this->assertTrue(Str::isUuid($id));

        //testing answer
        $response = $this->withHeaders([
            'Authorization' => $token,
        ])->json('POST', '/api/v2/discussion',
                    [
                        'parent' => $id,
                        'content'=>'answer',
                        'res_id'=>'eae9fd6f-7bac-4940-b80d-ad6cd6f433bf',
                        'res_type'=>'wbw',
                    ]);

        $response->assertOk();

        $id = $response['data']['id'];
        $this->assertTrue(Str::isUuid($id));
    }
}
