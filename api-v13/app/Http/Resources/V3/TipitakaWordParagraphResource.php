<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 按词形命中的一个三藏段落（`wbw_templates` 按段聚合 + `pali_texts` + `page_numbers`）。
 *
 * `text` 是整段正文的安全 HTML：只有 `<b>`（原文黑体，即注释书里被解释的词）与 `<mark>`（命中的词形），
 * 其余全部已转义，可直接渲染。`refs` 是各印本页码（每种版本取段内第一个），最后一条 `WP` 是 WikiPali
 * 段落号；`title` 是丛书缩写（缅甸版、PTS、WP 才有）。
 */
class TipitakaWordParagraphResource extends BaseResource
{
    /**
     * @return array{
     *     id: string,
     *     book: int,
     *     para: int,
     *     score: int,
     *     hits: int,
     *     title: string,
     *     level: int,
     *     path: list<array{book: int, para: int, title: string, level: int}>,
     *     text: string,
     *     refs: list<array{type: string, volume: int|null, page: int, title: string|null}>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => "{$this['book']}-{$this['para']}",
            'book' => $this['book'],
            'para' => $this['para'],
            'score' => $this['score'],
            'hits' => $this['hits'],
            'title' => $this['title'],
            'level' => $this['level'],
            'path' => $this['path'],
            'text' => $this['text'],
            'refs' => $this['refs'],
        ];
    }
}
