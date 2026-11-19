<?php

namespace App\Services\V3;

use App\Facades\RedisCache;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 三藏章节标题：按名字找章节（容错），或列出一本书的全部标题。
 *
 * 取代 v2 `SearchTitleController@index`（`GET /v2/search-title-index`）。v2 那份原样保留、不共用
 * （CLAUDE.md 铁律 2）。与 v2 的差别：
 *
 * - **返回坐标**（book、para）与体量、路径、上下章；v2 只回标题文字，拿到也定位不了。
 * - **容错匹配**：不分大小写与变音符号（`mahaparinibbana` 能找到 Mahāparinibbānasuttaṃ），
 *   去掉序号与括号里的丛书缩写，拼错一两个字母也能命中（按编辑距离打分）。v2 是
 *   `LIKE %key%`，还依赖 `title_en` 列——那一列去变音符号做得不干净（`3. Ānandattheragatha`）。
 * - **译名也能搜**：公开 channel 在 `progress_chapters.title` 里写的章节译名（如「大般涅槃经」）。
 * - 可按 book（及其中一章）列出全部标题，给 agent「先选书、再从整本书的标题里选章」用。
 *
 * 实现：3.7 万条标题与规范化后的匹配键缓存一天，匹配在 PHP 里做（库里没有 pg_trgm /
 * fuzzystrmatch，又不能装扩展）。先用三字母片段粗筛，再对候选算编辑距离。
 *
 * **定位（2026-11-11 定）**：本端点是 v2 `search-title-index` 的替代品。`q` 的容错匹配放在 PHP 里，
 * 只是为了先验证「按章节名定位」对 agent 的检索效果（评测见 agent-poc
 * `agent-tuning/docs/retrieval-recall-2026-11.md` 第七节）。**按名字找章节的正式实现要走 OpenSearch**：
 * 插件 `locate` 改调 `/v3/search`（granularity=chapter），计划见 `documents/search-recall.md` 4.6。
 * 届时这里的 PHP 匹配只保留给 v2 替代与小规模调用，不再扩展。
 */
class TipitakaChapterService
{
    /** 只有 level < 8 的段落是标题（与 v2、IndexTipitaka 同口径） */
    private const MAX_TITLE_LEVEL = 8;

    /** 缓存键带版本号：catalog 的结构变了就改版本号，不用等过期 */
    private const CATALOG_KEY = '/v3/tipitaka-chapters/catalog/v1';

    private const CATALOG_TTL = 86400;

    /** channels.status：全网公开。只有公开 channel 的章节译名才能被搜到、被返回 */
    private const STATUS_PUBLIC = 30;

    /** 书的 tag → layer。一本书有多个时按这个顺序取第一个 */
    private const LAYER_TAGS = ['mūla' => 'mula', 'aṭṭhakathā' => 'atthakatha', 'ṭīkā' => 'tika', 'añña' => 'anna'];

    /** 没有任何 layer tag 的书 */
    private const DEFAULT_LAYER = 'anna';

    private const SCORE_EXACT = 1.0;

    private const SCORE_PREFIX = 0.95;

    private const SCORE_CONTAINS = 0.9;

    /** 编辑距离匹配的最高分（低于「包含」：拼错的永远排在拼对的后面），乘以片段相似度 */
    private const SCORE_FUZZY_MAX = 0.85;

    /**
     * 片段相似度（1 - 距离/查询长度）低于这个就不算命中：20 个字母最多错 4 个。
     *
     * 0.75 太松：`etadaggapali` 会因为共有词尾 `-vaggapali` 命中 `(DN) Mahāvaggapāḷi`。
     */
    private const FUZZY_MIN_SIMILARITY = 0.8;

    /** 粗筛：查询的三字母片段至少有这么大比例出现在标题里，才算编辑距离 */
    private const TRIGRAM_MIN_RATIO = 0.5;

    /** 变音符号 → 拉丁字母（巴利罗马转写用到的全部） */
    private const FOLD = [
        'ā' => 'a', 'ī' => 'i', 'ū' => 'u', 'ṅ' => 'n', 'ñ' => 'n', 'ṇ' => 'n', 'ṭ' => 't',
        'ḍ' => 'd', 'ḷ' => 'l', 'ṃ' => 'm', 'ṁ' => 'm', 'ŋ' => 'm', 'ḥ' => 'h', 'ṛ' => 'r', 'ś' => 's', 'ṣ' => 's',
    ];

    /**
     * 找章节。给 q 按匹配度排序（同一档里根本典在前，再按与整个标题的接近度、大章在前、经文顺序）；
     * 不给 q 按经文顺序。
     *
     * @param  array{q?: string, book?: int, para?: int, layer?: string, max_level?: int}  $filters
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function search(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $rows = $this->filter($this->catalog(), $filters);

        $query = isset($filters['q']) ? self::normalize($filters['q']) : null;
        if ($query !== null) {
            $rows = $this->rank($rows, $query);
        }

        $total = count($rows);
        $items = $this->hydrate(array_slice($rows, ($page - 1) * $perPage, $perPage), $query !== null);

        return new LengthAwarePaginator($items, $total, $perPage, $page);
    }

    /**
     * 名字规范化成匹配键：小写、去变音符号、去括号里的丛书缩写（`(DN)`）、去序号与一切非字母。
     *
     * 汉字也是字母，保留；简繁不转换（同一章常有简繁两种译名）。
     */
    public static function normalize(string $text): string
    {
        $text = preg_replace('/\([^)]*\)|\[[^\]]*\]/u', '', mb_strtolower($text)) ?? '';

        return preg_replace('/[^\p{L}]+/u', '', strtr($text, self::FOLD)) ?? '';
    }

    /**
     * 一个匹配键对一个标题键打分，返回 [匹配度, 接近度]；匹配度 0 = 不算命中。
     *
     * 匹配度只分几档，便于解释也便于排序：相等 1、开头相同 0.95、包含 0.9、拼错的按片段相似度
     * 最高 0.85（取两位小数）。接近度是查询与整个标题有多像（0–1），只在同一档、同一层里决定先后——
     * 比如拼错的 `anandatheragatha` 对 Ānandattheragāthā 与 Girimānandattheragāthā 片段距离相同，
     * 靠它把前者排前。
     *
     * 非 ASCII 的键（汉字）只做包含匹配：`levenshtein()` 按字节算，对多字节字符没有意义。
     *
     * @return array{0: float, 1: float}
     */
    public static function score(string $query, string $title): array
    {
        if ($query === '' || $title === '') {
            return [0.0, 0.0];
        }
        $position = strpos($title, $query);
        if ($position !== false) {
            $closeness = strlen($query) / strlen($title);
            $grade = match (true) {
                $title === $query => self::SCORE_EXACT,
                $position === 0 => self::SCORE_PREFIX,
                default => self::SCORE_CONTAINS,
            };

            return [$grade, $closeness];
        }
        if (! mb_check_encoding($query, 'ASCII') || ! mb_check_encoding($title, 'ASCII')) {
            return [0.0, 0.0];
        }

        return self::fuzzyScore($query, $title);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function fuzzyScore(string $query, string $title): array
    {
        $miss = [0.0, 0.0];
        $queryLength = strlen($query);
        $titleLength = strlen($title);
        if ($queryLength < 4 || $titleLength < $queryLength * 0.6) {
            return $miss;
        }

        $trigrams = [];
        for ($i = 0; $i <= $queryLength - 3; $i++) {
            $trigrams[substr($query, $i, 3)] = true;
        }
        $shared = 0;
        foreach (array_keys($trigrams) as $trigram) {
            if (str_contains($title, $trigram)) {
                $shared++;
            }
        }
        if ($shared < count($trigrams) * self::TRIGRAM_MIN_RATIO) {
            return $miss;
        }

        $best = $queryLength;
        $lastStart = max(0, $titleLength - $queryLength + 1);
        for ($start = 0; $start <= $lastStart; $start++) {
            foreach ([$queryLength - 1, $queryLength, $queryLength + 1] as $width) {
                $best = min($best, levenshtein($query, substr($title, $start, $width)));
            }
        }
        $similarity = 1 - $best / $queryLength;
        if ($similarity < self::FUZZY_MIN_SIMILARITY) {
            return $miss;
        }
        $closeness = max(0.0, 1 - levenshtein($query, $title) / max($queryLength, $titleLength));

        return [round(self::SCORE_FUZZY_MAX * $similarity, 2), $closeness];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array{q?: string, book?: int, para?: int, layer?: string, max_level?: int}  $filters
     * @return list<array<string, mixed>>
     */
    private function filter(array $rows, array $filters): array
    {
        $book = $filters['book'] ?? null;
        $layers = isset($filters['layer']) ? explode(',', $filters['layer']) : null;
        $maxLevel = $filters['max_level'] ?? null;

        $range = null;
        if ($book !== null && isset($filters['para'])) {
            $range = $this->subtreeRange($rows, (int) $book, (int) $filters['para']);
        }

        return array_values(array_filter($rows, fn (array $row) => ($book === null || $row['book'] === (int) $book)
            && ($range === null || ($row['para'] >= $range[0] && $row['para'] < $range[1]))
            && ($layers === null || in_array($row['layer'], $layers, true))
            && ($maxLevel === null || $row['level'] <= $maxLevel)));
    }

    /**
     * 一章在书里覆盖的段落范围 [起, 止)。章不存在时返回空范围。
     *
     * @param  list<array<string, mixed>>  $rows
     * @return array{0: int, 1: int}
     */
    private function subtreeRange(array $rows, int $book, int $para): array
    {
        foreach ($rows as $row) {
            if ($row['book'] === $book && $row['para'] === $para) {
                return [$para, $para + max(1, $row['paragraphs'])];
            }
        }

        return [0, 0];
    }

    /**
     * 按匹配度排序，去掉不命中的。巴利名与各个译名取最高分。
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function rank(array $rows, string $query): array
    {
        $layerOrder = array_flip(array_values(self::LAYER_TAGS));
        $hits = [];
        foreach ($rows as $row) {
            $best = self::score($query, $row['key']);
            foreach ($row['translations'] as $translation) {
                $best = max($best, self::score($query, $translation['key']));
            }
            if ($best[0] > 0) {
                [$row['score'], $row['closeness']] = $best;
                $hits[] = $row;
            }
        }
        usort($hits, fn (array $a, array $b) => [$b['score'], $layerOrder[$a['layer']], $b['closeness'], $a['level'], $a['book'], $a['para']]
            <=> [$a['score'], $layerOrder[$b['layer']], $a['closeness'], $b['level'], $b['book'], $b['para']]);

        return $hits;
    }

    /**
     * 给一页补上路径（只有这一页查库，整页一次）。
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function hydrate(array $rows, bool $withScore): array
    {
        $paths = PaliText::query()
            ->whereKey(array_column($rows, 'id'))
            ->pluck('path', 'id');

        return array_map(function (array $row) use ($paths, $withScore) {
            $item = [
                'book' => $row['book'],
                'para' => $row['para'],
                'level' => $row['level'],
                'title' => $row['title'],
                'translations' => array_map(fn (array $t) => ['lang' => $t['lang'], 'title' => $t['title']], $row['translations']),
                'layer' => $row['layer'],
                'paragraphs' => $row['paragraphs'],
                'chars' => $row['chars'],
                'parent' => $row['parent'],
                'prev' => $row['prev'],
                'next' => $row['next'],
                'path' => self::decodePath($paths[$row['id']] ?? null),
            ];
            if ($withScore) {
                $item['score'] = $row['score'];
            }

            return $item;
        }, $rows);
    }

    /**
     * @return list<array{book: int, para: int, title: string, level: int}>
     */
    private static function decodePath(?string $json): array
    {
        $nodes = json_decode($json ?? '[]', true);
        if (! is_array($nodes)) {
            return [];
        }

        return array_values(array_map(fn (array $node) => [
            'book' => (int) ($node['book'] ?? 0),
            'para' => (int) ($node['paragraph'] ?? 0),
            'title' => (string) ($node['title'] ?? ''),
            'level' => (int) ($node['level'] ?? 0),
        ], array_filter($nodes, 'is_array')));
    }

    /**
     * 全部标题（经文顺序），带匹配键、layer、公开译名。缓存一天。
     *
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return RedisCache::remember(self::CATALOG_KEY, self::CATALOG_TTL, function () {
            $layers = $this->bookLayers();
            $translations = $this->translations();

            return PaliText::query()
                ->where('level', '<', self::MAX_TITLE_LEVEL)
                ->orderBy('book')
                ->orderBy('paragraph')
                ->get(['id', 'book', 'paragraph', 'level', 'toc', 'chapter_len', 'chapter_strlen', 'parent', 'prev_chapter', 'next_chapter'])
                ->map(function (PaliText $row) use ($layers, $translations) {
                    $title = trim((string) $row->toc);
                    $key = self::normalize($title);
                    $own = array_values(array_filter(
                        $translations["{$row->book}-{$row->paragraph}"] ?? [],
                        fn (array $t) => $t['key'] !== $key,
                    ));

                    return [
                        'id' => (int) $row->id,
                        'book' => (int) $row->book,
                        'para' => (int) $row->paragraph,
                        'level' => (int) $row->level,
                        'title' => $title,
                        'key' => $key,
                        'translations' => $own,
                        'layer' => $layers[(int) $row->book] ?? self::DEFAULT_LAYER,
                        'paragraphs' => (int) $row->chapter_len,
                        'chars' => (int) $row->chapter_strlen,
                        'parent' => self::chapterOrNull($row->parent),
                        'prev' => self::chapterOrNull($row->prev_chapter),
                        'next' => self::chapterOrNull($row->next_chapter),
                    ];
                })
                ->all();
        });
    }

    /** 上下章、父章的段号；库里用 0 / -1 表示没有 */
    private static function chapterOrNull(mixed $para): ?int
    {
        return $para !== null && (int) $para > 0 ? (int) $para : null;
    }

    /**
     * 每本书属于哪一层：取书（level 1 那一段）的 tag。
     *
     * @return array<int, string>
     */
    private function bookLayers(): array
    {
        $layerTags = fn (Builder $q) => $q->whereIn('name', array_keys(self::LAYER_TAGS));
        $books = PaliText::query()
            ->where('level', 1)
            ->whereHas('tagMaps.tags', $layerTags)
            ->with(['tagMaps.tags' => $layerTags])
            ->get(['uid', 'book']);

        $order = array_flip(array_keys(self::LAYER_TAGS));
        $layers = [];
        foreach ($books as $book) {
            foreach ($book->tagMaps as $map) {
                $name = $map->tags?->name;
                $current = $layers[(int) $book->book] ?? null;
                if ($name !== null && ($current === null || $order[$name] < $order[$current])) {
                    $layers[(int) $book->book] = $name;
                }
            }
        }

        return array_map(fn (string $tag) => self::LAYER_TAGS[$tag], $layers);
    }

    /**
     * 公开 channel 写的章节译名，按「book-para」分组，同一章同一个匹配键只留一条。
     *
     * 排除巴利原文 channel 的标题（`lang = pali`）、逐词 channel 存进来的 JSON（以 `[` 开头）。
     *
     * @return array<string, list<array{lang: string, title: string, key: string}>>
     */
    private function translations(): array
    {
        $rows = ProgressChapter::query()
            ->whereHas('channel', fn (Builder $q) => $q->where('status', self::STATUS_PUBLIC))
            ->where('lang', '<>', 'pali')
            ->whereRaw("trim(title) <> ''")
            ->where('title', 'not like', '[%')
            ->orderBy('book')
            ->orderBy('para')
            ->orderBy('lang')
            ->get(['book', 'para', 'lang', 'title']);

        $grouped = [];
        foreach ($rows as $row) {
            $title = trim((string) $row->title);
            $key = self::normalize($title);
            $chapter = "{$row->book}-{$row->para}";
            if ($key === '' || isset($grouped[$chapter][$key])) {
                continue;
            }
            $grouped[$chapter][$key] = ['lang' => (string) $row->lang, 'title' => $title, 'key' => $key];
        }

        return array_map('array_values', $grouped);
    }
}
