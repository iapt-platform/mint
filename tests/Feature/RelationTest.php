<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Relation;

class RelationTest extends TestCase
{
    private $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2NjgyMzE3MTksImV4cCI6MTY5OTc2NzcxOSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOiI0In0.LV4ItC5VCqXpbKIXT1zePcnfi-heCf3Df63w7qbXsT1i5KJtwJJC938CLgANjqwcQFa3lrR5TqvT1kkqD-Mmgg';

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $response = $this->get('/api/v2/relation?search=a');

        $response->assertStatus(200);
    }

    public function test_store()
    {
        //testing store
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('POST', '/api/v2/relation',
                    [
                        'name'=>'isv',
                        'case'=>['gen'],
                    ]);

        $response->assertOk();

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('POST', '/api/v2/relation',
                    [
                        'name'=>'iov',
                    ]);

        $response->assertOk();
    }

    public function test_show()
    {
        //testing store
        $id = Relation::value('id');
        $response = $this->get("/api/v2/relation/{$id}");

        $response->assertStatus(200);
    }

    public function test_update()
    {
        //testing store
        sleep(1);
        $id = Relation::value('id');
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('PUT', "/api/v2/relation/{$id}",
                [
                    'name'=>'iov_2',
                    'case'=>['inst'],
                    'to'=>['inst'],
                ]);

        $response->assertOk();
    }

    public function test_delete(){

        //testing delete
        $id = Relation::value("id");
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->delete("/api/v2/relation/{$id}");

        $response->assertOk();
    }

}
