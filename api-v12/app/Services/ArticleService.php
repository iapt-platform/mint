<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCollection;

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

    public function articlesInAnthology($anthologyId)
    {
        $inCollection = ArticleCollection::where('collect_id', $anthologyId)
            ->select('article_id')
            ->get()->toArray();
        return array_map(fn($item) => $item['article_id'], $inCollection);
    }
}
