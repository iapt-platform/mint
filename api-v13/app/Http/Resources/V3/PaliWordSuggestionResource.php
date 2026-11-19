<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一个补全候选：三藏里出现过的词形（`word_indices`）。
 *
 * `count` 是总出现次数，`bold` 是其中以黑体出现的次数（注释书里被解释的词）。
 */
class PaliWordSuggestionResource extends BaseResource
{
    /**
     * @return array{word: string, count: int, bold: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'word' => $this['word'],
            'count' => $this['count'],
            'bold' => $this['bold'],
        ];
    }
}
