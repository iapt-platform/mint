<?php

namespace App\Http\Resources\V3;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;

/**
 * 词汇表的一条（`dhamma_terms` 表里系统 channel 的术语）。只给客户端本地查词要用的字段；`id` 是术语 guid。
 *
 * @mixin DhammaTerm
 */
class GlossaryEntryResource extends BaseResource
{
    /**
     * @return array{
     *     id: string,
     *     word: string,
     *     tag: string|null,
     *     meaning: string,
     *     other_meaning: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        $tag = trim((string) $this->tag);
        $other = trim((string) $this->other_meaning);

        return [
            'id' => (string) $this->guid,
            'word' => (string) $this->word,
            'tag' => $tag === '' ? null : $tag,
            'meaning' => (string) $this->meaning,
            'other_meaning' => $other === '' ? null : $other,
        ];
    }
}
