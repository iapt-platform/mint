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
    public function sentenceIds(string $id): ?array
    {
        $article = $this->getRawById($id);
        if (empty($article->content)) {
            return null;
        }
        $sentenceIds = $this->extractBracesContent($article->content);
        return $sentenceIds;
    }

    /**
     * 提取字符串中 {{ }} 之间的内容
     *
     * @param string $text
     * @return array
     */
    public function extractBracesContent(string $text): array
    {
        preg_match_all('/\{\{\s*(.*?)\s*\}\}/', $text, $matches);

        return $matches[1] ?? [];
    }
}
