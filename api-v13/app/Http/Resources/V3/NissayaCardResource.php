<?php

namespace App\Http\Resources\V3;

use App\Services\V3\NissayaCardService;
use Illuminate\Http\Request;

/**
 * 缅文 nissaya 词尾卡片（{@see NissayaCardService}）。
 *
 * - `term`：词尾对应的语法术语，`body` 是新渲染器的节点树；没有这条术语时为 null
 * - `relations`：这个词尾表达的语法关系，每条带关系名的本地译名、来源 / 目标的格位（带本地译名）与拼写、
 *   含义分类（`note` 是纯文本）、翻译建议
 * - `lang`：实际用到的语言（没有该语言的语法术语表时是 en）
 */
class NissayaCardResource extends BaseResource
{
    /**
     * @return array{
     *     ending: string,
     *     lang: string,
     *     term: array{id: string, word: string, tag: string|null, meaning: string, body: list<MarkupNode>}|null,
     *     relations: list<array{
     *         relation: string,
     *         relation_label: string|null,
     *         from: array{cases: list<array{case: string, label: string|null}>, spell: string|null}|null,
     *         to: array{cases: list<array{case: string, label: string|null}>, spell: string|null}|null,
     *         category: array{name: string, meaning: string, note: string}|null,
     *         local_endings: list<string>
     *     }>
     * }
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
