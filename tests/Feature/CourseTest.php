<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    private $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2NjgyMzE3MTksImV4cCI6MTY5OTc2NzcxOSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOiI0In0.LV4ItC5VCqXpbKIXT1zePcnfi-heCf3Df63w7qbXsT1i5KJtwJJC938CLgANjqwcQFa3lrR5TqvT1kkqD-Mmgg';
    public function test_index(){
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/v2/course?view=create');

        $response->assertOk();

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/v2/course?view=study');

        $response->assertOk();
    }
    public function test_store()
    {
        //testing store
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('POST', '/api/v2/course',
                    [
                        'title'=>'course1',
                        'studio'=>'visuddhinanda'
                    ]);

        $response->assertOk();
    }

    public function test_delete(){

        //testing delete
        $member = Course::where('title','course1')
                        ->first();
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->delete('/api/v2/course/'.$member->id);

        $response->assertOk();
    }
}
