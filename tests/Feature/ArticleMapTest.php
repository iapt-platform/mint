<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Collection;


class ArticleMapTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $collection = Collection::first();
        $response = $this->get('/api/v2/article-map?view=anthology&id='.$collection->collect_id);

        $response->assertStatus(200);

        $response = $this->get('/api/v2/article-map?view=article&id='.$collection->article_id);

        $response->assertStatus(200);
    }
}
