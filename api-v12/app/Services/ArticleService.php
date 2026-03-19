<?php

namespace App\Services;

use App\Models\Article;

class ArticleService
{
    public function getRawById(string $id)
    {
        return Article::find($id);
    }
    public function getRawByTitle(string $title)
    {
        $article = Article::where('title', $title)->first();
        return $article;
    }
}
