<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchPaliWbwTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store()
    {
        $response = $this->post('/api/v2/search-pali-wbw',
                    [
                        'view'=>'pali',
                        'words'=>['jātāni','jātā'],
                    ]);

        $response->assertOk();
    }
}
