<?php

namespace App\Services\V3;

use App\Models\DhammaTerm;
use App\Models\NissayaEnding;
use App\Models\Relation;
use App\Services\ChannelService as ChannelLookup;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use Illuminate\Support\Collection;

/**
 * 缅文 nissaya 词尾卡片（只读）：这个词尾对应的语法术语，以及它表达的语法关系。
 *
 * 取代 v2 `nissaya-card/{ending}`（`NissayaCardController@show`）。数据：
 * - 术语取语法术语系统 channel `_System_Grammar_Term_{lang}_`，没有就用 en（与 v2 相同）
 * - 词尾 → 关系：`nissaya_endings.relation`；关系的格位 / 分类：`relations`
 * - 翻译建议：同一关系在 `lang` 下的其它词尾（from 为空或与关系的 from 相同）
 *
 * 与 v2 的差别：只给结构化数据（v2 另有 mustache 渲染的 markdown 分支，表头文字也写死在后端）；
 * 不再拼 dashboard 链接（前端自己拼）；术语一次批量查（v2 逐个格位查，N+1）；
 * 词尾说明用新渲染器给节点树（v2 是旧渲染器的 HTML）。
 */
class NissayaCardService
{
    private const GRAMMAR_CHANNEL = '_System_Grammar_Term_';

    public function __construct(
        private readonly ChannelLookup $channels,
        private readonly MarkupService $markup,
    ) {}

    /**
     * @return array{
     *     ending: string,
     *     lang: string,
     *     term: array{id: string, word: string, tag: string|null, meaning: string, body: list<array<string, mixed>>}|null,
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
    public function card(string $ending, string $lang): array
    {
        [$channel, $servedLang] = $this->grammarChannel($lang);

        $relationNames = NissayaEnding::where('ending', $ending)
            ->whereNotNull('relation')
            ->where('relation', '<>', '')
            ->pluck('relation')
            ->unique()
            ->values();
        $relations = $relationNames->isEmpty()
            ? collect()
            : Relation::whereIn('name', $relationNames)->orderBy('name')->get(['name', 'from', 'to', 'category']);

        // 一次查出卡片上要用的全部术语：词尾、关系名、格位、分类
        $words = collect([$ending])
            ->merge($relations->pluck('name'))
            ->merge($relations->pluck('category')->filter())
            ->merge($relations->flatMap(fn (Relation $r) => [
                ...($this->decode($r->from)['case'] ?? []),
                ...($this->decode($r->to)['case'] ?? []),
            ]))
            ->filter(fn ($w) => is_string($w) && $w !== '')
            ->unique()
            ->values();
        $terms = $channel === false || $words->isEmpty()
            ? collect()
            : DhammaTerm::where('channal', $channel)->whereIn('word', $words)
                ->get(['guid', 'word', 'tag', 'meaning', 'note', 'content_type', 'channal'])
                ->keyBy('word');

        $localEndings = $relations->isEmpty()
            ? collect()
            : NissayaEnding::whereIn('relation', $relations->pluck('name'))
                ->where('lang', $lang)
                ->get(['ending', 'relation', 'from']);

        $rendered = $this->render($terms->get($ending), $relations, $terms);

        return [
            'ending' => $ending,
            'lang' => $servedLang,
            'term' => $this->termOf($terms->get($ending), $rendered['body']),
            'relations' => $relations->map(fn (Relation $relation) => $this->relationRow(
                $relation,
                $terms,
                $localEndings,
                $rendered['categories'],
            ))->values()->all(),
        ];
    }

    /**
     * @return array{0: string|false, 1: string} 语法术语 channel 的 uid 与实际用到的语言
     */
    private function grammarChannel(string $lang): array
    {
        $channel = $this->channels->systemUid(self::GRAMMAR_CHANNEL.strtolower($lang).'_');
        if ($channel !== false) {
            return [$channel, $lang];
        }

        return [$this->channels->systemUid(self::GRAMMAR_CHANNEL.'en_'), 'en'];
    }

    /**
     * 词尾说明（节点树）与各分类说明（纯文本）一次渲染。
     *
     * @param  Collection<int, Relation>  $relations
     * @param  Collection<string, DhammaTerm>  $terms
     * @return array{body: list<array<string, mixed>>, categories: array<string, string>}
     */
    private function render(?DhammaTerm $endingTerm, Collection $relations, Collection $terms): array
    {
        $tree = [];
        if ($endingTerm && trim((string) $endingTerm->note) !== '') {
            $tree['ending'] = $this->item($endingTerm);
        }
        $text = [];
        foreach ($relations->pluck('category')->filter()->unique() as $name) {
            $term = $terms->get($name);
            if ($term && trim((string) $term->note) !== '') {
                $text[$name] = $this->item($term);
            }
        }

        return [
            'body' => $tree === [] ? [] : $this->markup->renderMany($tree, new RenderOptions(format: Format::Tree))['ending'],
            'categories' => $text === [] ? [] : array_map('strval', $this->markup->renderMany($text, new RenderOptions(format: Format::Text))),
        ];
    }

    /**
     * @return array{source: string, channels: list<string>, contentType: string}
     */
    private function item(DhammaTerm $term): array
    {
        return [
            'source' => (string) $term->note,
            'channels' => $term->channal ? [$term->channal] : [],
            'contentType' => $term->effectiveContentType(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $body
     * @return array{id: string, word: string, tag: string|null, meaning: string, body: list<array<string, mixed>>}|null
     */
    private function termOf(?DhammaTerm $term, array $body): ?array
    {
        if ($term === null) {
            return null;
        }
        $tag = trim((string) $term->tag);

        return [
            'id' => (string) $term->guid,
            'word' => (string) $term->word,
            'tag' => $tag === '' ? null : $tag,
            'meaning' => (string) $term->meaning,
            'body' => $body,
        ];
    }

    /**
     * @param  Collection<string, DhammaTerm>  $terms
     * @param  Collection<int, NissayaEnding>  $localEndings
     * @param  array<string, string>  $categoryNotes
     * @return array{relation: string, relation_label: string|null, from: array{cases: list<array{case: string, label: string|null}>, spell: string|null}|null, to: array{cases: list<array{case: string, label: string|null}>, spell: string|null}|null, category: array{name: string, meaning: string, note: string}|null, local_endings: list<string>}
     */
    private function relationRow(Relation $relation, Collection $terms, Collection $localEndings, array $categoryNotes): array
    {
        $category = null;
        if (! empty($relation->category)) {
            $term = $terms->get($relation->category);
            $category = [
                'name' => (string) $relation->category,
                'meaning' => $term ? (string) $term->meaning : (string) $relation->category,
                'note' => trim($categoryNotes[$relation->category] ?? ($term ? '' : (string) $relation->category)),
            ];
        }

        // 翻译建议：同一关系、该语言的词尾；词尾的 from 为空，或与关系的 from 完全相同（与 v2 一致，比较 JSON 原文）
        $suggestions = $localEndings
            ->filter(fn (NissayaEnding $e) => $e->relation === $relation->name
                && (empty($e->from) || $this->raw($e->from) === $this->raw($relation->from)))
            ->pluck('ending')
            ->unique()
            ->values()
            ->all();

        return [
            'relation' => (string) $relation->name,
            'relation_label' => $terms->get($relation->name)?->meaning,
            'from' => $this->side($relation->from, $terms),
            'to' => $this->side($relation->to, $terms),
            'category' => $category,
            'local_endings' => $suggestions,
        ];
    }

    /**
     * 关系的一端（from / to）：格位（带本地译名）与拼写。都没有返回 null。
     *
     * @param  Collection<string, DhammaTerm>  $terms
     * @return array{cases: list<array{case: string, label: string|null}>, spell: string|null}|null
     */
    private function side(mixed $value, Collection $terms): ?array
    {
        $data = $this->decode($value);
        $cases = array_values(array_filter((array) ($data['case'] ?? []), 'is_string'));
        $spell = isset($data['spell']) && $data['spell'] !== '' ? (string) $data['spell'] : null;
        if ($cases === [] && $spell === null) {
            return null;
        }

        return [
            'cases' => array_map(fn (string $case) => ['case' => $case, 'label' => $terms->get($case)?->meaning], $cases),
            'spell' => $spell,
        ];
    }

    /**
     * json 列可能是字符串（库里原样）或已解码的数组。
     *
     * @return array<string, mixed>
     */
    private function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function raw(mixed $value): string
    {
        return is_string($value) ? $value : (string) json_encode($value);
    }
}
