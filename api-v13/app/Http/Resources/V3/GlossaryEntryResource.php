<?php

namespace App\Http\Resources\V3;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;

/**
 * 词汇表的一条（`dhamma_terms` 表里系统 channel 的术语）。只给客户端本地查词要用的字段。
 *
 * `id`（术语 guid）只在语法术语表（grammar）里给：v6 悬停 / 语法手册凭它取 `/v3/terms/{id}`。
 * 社区译名表（community）没有任何客户端用 id，而上万个 uuid 是整张表里最大、最压不动的一块，所以不给。
 *
 * @mixin DhammaTerm
 */
class GlossaryEntryResource extends BaseResource
{
    /**
     * @return array{
     *     id?: string,
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
            'id' => $this->when($request->route('kind') === 'grammar', fn () => (string) $this->guid),
            'word' => (string) $this->word,
            'tag' => $tag === '' ? null : $tag,
            'meaning' => (string) $this->meaning,
            'other_meaning' => $other === '' ? null : $other,
        ];
    }
}
