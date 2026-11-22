<?php

namespace App\Services\V3;

use App\Facades\RedisCache;
use App\Models\PaliText;
use App\Services\PaliContentService;

/**
 * 巴利原文段落表 `pali_texts` 的业务逻辑（2026-11-22 起，新代码里 pali_texts 的读取都放这里）。
 *
 * 这张表是经文静态数据，只有重新导入（`install:palitext` / `upgrade:palitext`）才会变，
 * 所以按书缓存一个月，进程内再记一份：
 *
 * - 标题行（level < 8）：一本书一个 key，阅读器算章节区间、导出排标题、章节范围都从这里取；
 * - 全部段落的 level / class / 字节数：按 {@see self::CHUNK} 段一块，一本书最多一万六千段，
 *   整本放一个 key 有四百多 KB，读一章只要其中一两块。
 *
 * 重新导入后调 {@see self::forgetBook()}；改了缓存的结构就把 {@see self::VERSION} 加一（旧 key 自然过期）。
 *
 * 旧的 `App\Services\PaliTextService`（v2 在用）原样保留，不往那边加东西。
 */
class PaliTextService
{
    /** 缓存结构版本，改了缓存里存的字段就加一。 */
    public const VERSION = 1;

    /** 一个月。 */
    public const TTL = 30 * 24 * 3600;

    /** 段落按多少段一块缓存。 */
    public const CHUNK = 1000;

    /** 标题段的 level 上限（不含）：1–7 是书 / 章 / 节等可导航节点，100 是正文。 */
    public const HEADING_MAX_LEVEL = 8;

    /** @var array<int, array<int, array{level: int, chapter_len: int, chapter_strlen: int, parent: int, toc: string}>> */
    private array $headings = [];

    /** @var array<string, array<int, array{int, string, int}>> */
    private array $chunks = [];

    /** @var array<int, array<int, int>> */
    private array $flatChapters = [];

    /**
     * 一本书的全部标题段（level < 8），按段落号升序：段落号 => 标题信息。
     *
     * @return array<int, array{level: int, chapter_len: int, chapter_strlen: int, parent: int, toc: string}>
     */
    public function headings(int $book): array
    {
        if (isset($this->headings[$book])) {
            return $this->headings[$book];
        }
        // 存成列表（段落号放在行里）：键值对的 JSON 在键恰好是 0、1、2… 时会变成数组、读回来丢键
        $rows = RedisCache::remember(
            self::headingsKey($book),
            self::TTL,
            fn () => PaliText::where('book', $book)
                ->where('level', '<', self::HEADING_MAX_LEVEL)
                ->orderBy('paragraph')
                ->get(['paragraph', 'level', 'chapter_len', 'chapter_strlen', 'parent', 'toc'])
                ->map(fn (PaliText $row) => [
                    (int) $row->paragraph, (int) $row->level, (int) $row->chapter_len,
                    (int) $row->chapter_strlen, (int) $row->parent, (string) $row->toc,
                ])
                ->all()
        );
        $headings = [];
        foreach ($rows as [$para, $level, $chapterLen, $chapterStrlen, $parent, $toc]) {
            $headings[$para] = [
                'level' => $level,
                'chapter_len' => $chapterLen,
                'chapter_strlen' => $chapterStrlen,
                'parent' => $parent,
                'toc' => $toc,
            ];
        }

        return $this->headings[$book] = $headings;
    }

    /**
     * 某段是标题段时返回它的标题信息，否则 null。
     *
     * @return array{level: int, chapter_len: int, chapter_strlen: int, parent: int, toc: string}|null
     */
    public function heading(int $book, int $para): ?array
    {
        return $this->headings($book)[$para] ?? null;
    }

    /**
     * 段落是章节标题时返回标题级别（1–7），否则 0。
     */
    public function headingLevel(int $book, int $para): int
    {
        return $this->heading($book, $para)['level'] ?? 0;
    }

    /**
     * 某段所在的书（level = 1 且段落号不大于它的最后一个标题段），没有返回 null。
     *
     * @return array{para: int, level: int, chapter_len: int, chapter_strlen: int, parent: int, toc: string}|null
     */
    public function bookStart(int $book, int $para): ?array
    {
        $found = null;
        foreach ($this->headings($book) as $paragraph => $heading) {
            if ($paragraph > $para) {
                break;
            }
            if ($heading['level'] === 1) {
                $found = ['para' => $paragraph] + $heading;
            }
        }

        return $found;
    }

    /**
     * 章节起始段落号 → 段落闭区间 [起, 止]，长度取 chapter_len。
     * 正文段（不是标题）没有 chapter_len，区间只有它自己；pali_texts 里没有这段返回 null。
     *
     * @return array{int, int}|null
     */
    public function chapterRange(int $book, int $chapter): ?array
    {
        $heading = $this->heading($book, $chapter);
        if ($heading === null) {
            return $this->paragraph($book, $chapter) === null ? null : [$chapter, $chapter];
        }

        return [$chapter, $chapter + max(1, $heading['chapter_len']) - 1];
    }

    /**
     * 平铺的章节：每个标题段（level < 8）是一章的开头，到下一个标题段之前为止；章节按顺序排、不嵌套，
     * 一段只属于一章。最后一章到它自己的 chapter_len 为止。章节起点 => 终点（含）。
     *
     * 与 chapter_len 的「父章包含子章」不同，这是阅读缓存用的切法（{@see PaliContentService::readChapters()}）。
     *
     * @return array<int, int>
     */
    public function flatChapters(int $book): array
    {
        if (isset($this->flatChapters[$book])) {
            return $this->flatChapters[$book];
        }
        $chapters = [];
        $starts = array_keys($this->headings($book));
        foreach ($starts as $i => $start) {
            $chapters[$start] = isset($starts[$i + 1])
                ? $starts[$i + 1] - 1
                : $start + max(1, $this->headings($book)[$start]['chapter_len']) - 1;
        }

        return $this->flatChapters[$book] = $chapters;
    }

    /**
     * 某段所在平铺章节的起点（{@see self::flatChapters()}）；排在第一个标题段之前、或超出最后一章的返回 null。
     */
    public function flatChapterOf(int $book, int $para): ?int
    {
        $found = null;
        foreach ($this->flatChapters($book) as $start => $end) {
            if ($start > $para) {
                break;
            }
            $found = $para <= $end ? $start : null;
        }

        return $found;
    }

    /**
     * 区间内各段的 level / class / 字节数：段落号 => 信息。pali_texts 里没有的段不出现。
     *
     * @return array<int, array{level: int, class: string, length: int}>
     */
    public function paragraphs(int $book, int $from, int $to): array
    {
        $result = [];
        for ($chunk = intdiv(max(0, $from), self::CHUNK); $chunk <= intdiv(max(0, $to), self::CHUNK); $chunk++) {
            foreach ($this->chunk($book, $chunk) as $para => [$level, $class, $length]) {
                if ($para >= $from && $para <= $to) {
                    $result[$para] = ['level' => $level, 'class' => $class, 'length' => $length];
                }
            }
        }

        return $result;
    }

    /**
     * 单段的 level / class / 字节数，pali_texts 里没有返回 null。
     *
     * @return array{level: int, class: string, length: int}|null
     */
    public function paragraph(int $book, int $para): ?array
    {
        return $this->paragraphs($book, $para, $para)[$para] ?? null;
    }

    /**
     * 重新导入某本书后调用，删掉它的全部缓存。
     */
    public function forgetBook(int $book): void
    {
        RedisCache::forget(self::headingsKey($book));
        $max = (int) PaliText::where('book', $book)->max('paragraph');
        // 段数可能变少，多删一块兜底
        for ($chunk = 0; $chunk <= intdiv($max, self::CHUNK) + 1; $chunk++) {
            RedisCache::forget(self::chunkKey($book, $chunk));
        }
        unset($this->headings[$book], $this->flatChapters[$book]);
        $this->chunks = array_filter($this->chunks, fn ($key) => ! str_starts_with($key, "{$book}-"), ARRAY_FILTER_USE_KEY);
    }

    /**
     * 一块段落：段落号 => [level, class, 字节数]。缓存里存成 [段落号, level, class, 字节数] 的列表。
     *
     * @return array<int, array{int, string, int}>
     */
    private function chunk(int $book, int $chunk): array
    {
        $memo = "{$book}-{$chunk}";
        if (isset($this->chunks[$memo])) {
            return $this->chunks[$memo];
        }
        $rows = RedisCache::remember(
            self::chunkKey($book, $chunk),
            self::TTL,
            fn () => PaliText::where('book', $book)
                ->whereBetween('paragraph', [$chunk * self::CHUNK, ($chunk + 1) * self::CHUNK - 1])
                ->orderBy('paragraph')
                ->get(['paragraph', 'level', 'class', 'lenght'])
                ->map(fn (PaliText $row) => [(int) $row->paragraph, (int) $row->level, (string) $row->class, (int) $row->lenght])
                ->all()
        );
        $paragraphs = [];
        foreach ($rows as [$para, $level, $class, $length]) {
            $paragraphs[$para] = [$level, $class, $length];
        }

        return $this->chunks[$memo] = $paragraphs;
    }

    private static function headingsKey(int $book): string
    {
        return '/pali-text/v'.self::VERSION."/{$book}/headings";
    }

    private static function chunkKey(int $book, int $chunk): string
    {
        return '/pali-text/v'.self::VERSION."/{$book}/paragraphs/{$chunk}";
    }
}
