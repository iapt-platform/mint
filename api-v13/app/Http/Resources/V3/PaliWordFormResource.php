<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一个词根及其在三藏里出现过的词形（`word_indices` 表的计数）。
 *
 * `count` 是该词形的总出现次数，`bold` 是其中以黑体出现的次数（注释书里被解释的词）。
 */
class PaliWordFormResource extends BaseResource
{
    /**
     * @return array{
     *     base: string,
     *     forms: list<array{word: string, count: int, bold: int}>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'base' => (string) $this['base'],
            'forms' => $this['forms'],
        ];
    }
}
