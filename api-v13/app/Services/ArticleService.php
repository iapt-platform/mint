<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\Sentence;

use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\Log;
use App\Http\Api\ChannelApi;

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
     * 提取字符串中 {{1-2-3-4}} 格式的内容（四段数字）
     *
     * @param string $text
     * @return array
     */
    public function extractBracesContent(string $text): array
    {
        preg_match_all('/\{\{\s*(\d+-\d+-\d+-\d+)\s*\}\}/', $text, $matches);

        return $matches[1] ?? [];
    }

    public function articlesInAnthology(string $anthologyId)
    {
        $inCollection = ArticleCollection::where('collect_id', $anthologyId)
            ->select('article_id')
            ->get()->toArray();
        return array_map(fn($item) => $item['article_id'], $inCollection);
    }

    public function getArticle(string $id): array
    {
        $result = Article::where('uid', $id)->first();
        if (!$result) {
            Log::warning("没有查询到数据 id={$id}");
            return ['error' => "没有查询到数据 id={$id}", 'code' => 404];
        }

        return [
            'data' => new ArticleResource($result),
            'ok' => true
        ];
    }

    public function articleChannels(string $id): ?array
    {
        $sentences = $this->sentenceIds($id);
        if (!$sentences) {
            return null;
        }
        $query = [];
        foreach ($sentences as $value) {
            $ids = explode('-', $value);
            $query[] = $ids;
        }
        $fields = ['book_id', 'paragraph', 'word_start', 'word_end'];
        $publicChannelIds = Sentence::whereIns($fields, $query)
            ->where('strlen', '>', 0)
            ->where('status', 30)
            ->groupBy('channel_uid')
            ->select('channel_uid')
            ->get();
        $channels = [];
        foreach ($publicChannelIds as  $channel) {
            $channels[] = ChannelApi::getById($channel->channel_uid);
        }
        return $channels;
    }
}
