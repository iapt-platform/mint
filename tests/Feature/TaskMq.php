<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskMq extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store()
    {
        $response = $this->withHeaders([
            'Authorization' => env('TEST_USER_TOKEN'),
        ])->json('POST', '/api/v2/task',
                    [
                        'name'=>'test:md.render',
                        'param'=>[
                            'item'=>'bold',
                        ],
                    ]);

        $response->assertOk();
    }
}
