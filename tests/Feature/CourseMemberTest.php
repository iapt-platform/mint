<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\CourseMember;

class CourseMemberTest extends TestCase
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
        ])->get('/api/v2/course-member?view=course&id=8079d293-5057-449f-b8b2-6482531d2434');

        $response->assertOk();
    }
    public function test_store()
    {
        //testing store
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->json('POST', '/api/v2/course-member',
                    [
                        'user_id'=>'61f52926-e024-41f0-8be5-48a962560a23',
                        'course_id'=>'8079d293-5057-449f-b8b2-6482531d2434',
                        'role'=>'member',
                    ]);

        $response->assertOk();



    }

    public function test_delete(){

        //testing delete
        $member = CourseMember::where('user_id','61f52926-e024-41f0-8be5-48a962560a23')->first();
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->delete('/api/v2/course-member/'.$member->id);

        $response->assertOk();
    }
}
