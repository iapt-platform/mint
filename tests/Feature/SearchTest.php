<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $response = $this->get('/api/v2/search?key=samānasaṃvāsa&tags=sutta');

        $response->assertStatus(200);

        $response = $this->get('/api/v2/search?key=samānasaṃvāsa;vāsa&tags=sutta');

        $response->assertStatus(200);

        $response = $this->get('/api/v2/search?view=page&type=P&key=34&tags=sutta');

        $response->assertStatus(200);
    }
    public function test_book_list()
    {
        $response = $this->get('/api/v2/search-book-list?key=samānasaṃvāsa&tags=sutta');

        $response->assertStatus(200);
    }

}
