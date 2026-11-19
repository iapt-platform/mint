<?php

namespace App\Services\V3;

use App\Models\WordIndex;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * 巴利词的前缀补全（`word_indices`：三藏里出现过的每个词形及出现次数、黑体次数）。
 *
 * 前缀同时匹配原形与去变音形（`word_en`），所以不打变音符号也能补全（dhamma → dhammā）。
 * 排序：与输入完全相同的词在前，其余按出现次数降序（v2 按词长排，短而罕见的词形挤掉常用词）。
 */
class PaliWordSuggestionService
{
    /**
     * @return list<array{word: string, count: int, bold: int}>
     */
    public function suggest(string $prefix, int $limit): array
    {
        $prefix = mb_strtolower(trim($prefix), 'UTF-8');
        $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $prefix).'%';

        return WordIndex::query()
            ->where(fn (Builder $q) => $q->where('word', 'like', $like)->orWhere('word_en', 'like', $like))
            ->orderByRaw('(word = ? or word_en = ?) desc', [$prefix, $prefix])
            ->orderByDesc('count')
            ->orderBy('word')
            ->limit($limit)
            ->get(['word', 'count', 'bold'])
            ->map(fn (WordIndex $w) => [
                'word' => (string) $w->word,
                'count' => (int) $w->count,
                'bold' => (int) $w->bold,
            ])
            ->all();
    }
}
