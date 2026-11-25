<?php

namespace App\Services\RelatedParagraph;

use InvalidArgumentException;

/**
 * wbw_templates 里 `.ctl.` 锚点（CS 版 html 的 `<a name="...">`）的解析。
 *
 * 写法全表见 documents/related-paragraphs-rebuild.md §3.1（2026-11-25 全库扫描）：
 * 单号、完整区间、缩写区间、链式、倒序笔误、截断，可带 `_dn1` 型后缀；另有章标记
 * `dn1_3` / `an5_1_1` / `kn14b_17`、书名标记 `kn11`、页码标记 `P1.0023`。
 * 表外的写法抛异常——旧命令丢段就是静默跳过造成的。
 */
class AnchorParser
{
    public const KIND_PARA = 'para';

    public const KIND_CHAPTER = 'chapter';

    public const KIND_BOOK = 'book';

    public const KIND_PAGE = 'page';

    /**
     * @return array{kind: string, begin?: int, end?: int, suffix?: string|null, name?: string, preface?: bool, warning?: string|null}
     *
     * @throws InvalidArgumentException 表外写法
     */
    public static function parse(string $word): array
    {
        if (preg_match('/^[A-Z][0-9]/', $word)) {
            return ['kind' => self::KIND_PAGE];
        }

        if (preg_match('/^para([0-9][0-9-]*)(?:_([a-z]+[0-9]+))?$/', $word, $m)) {
            return ['kind' => self::KIND_PARA, 'suffix' => $m[2] ?? null] + self::parseNumbers($m[1], $word);
        }

        // 章标记：dn1_3、an5_1_1、kn14b_17（kn14b 是 kn14 的第二册，名字仍是 kn14）。
        // 只有一级且为 0 的（dn1_0）表示义注序言 → dn1_A。
        if (preg_match('/^([a-z]+[0-9]+)[a-z]?((?:_[0-9]+)+)$/', $word, $m)) {
            $numbers = explode('_', ltrim($m[2], '_'));

            return [
                'kind' => self::KIND_CHAPTER,
                'name' => $m[1],
                'preface' => count($numbers) === 1 && (int) $numbers[0] === 0,
            ];
        }

        if (preg_match('/^([a-z]+[0-9]+)[a-z]?$/', $word, $m)) {
            return ['kind' => self::KIND_BOOK, 'name' => $m[1]];
        }

        throw new InvalidArgumentException("无法解析的锚点：{$word}");
    }

    /**
     * @return array{begin: int, end: int, warning: string|null}
     */
    private static function parseNumbers(string $body, string $word): array
    {
        // para179-：右端缺失，全库仅 book 11 para 1577 一处
        if (str_ends_with($body, '-')) {
            $begin = (int) rtrim($body, '-');
            if (str_contains(rtrim($body, '-'), '-')) {
                throw new InvalidArgumentException("无法解析的锚点：{$word}");
            }

            return ['begin' => $begin, 'end' => $begin, 'warning' => "截断区间 {$word}，按单号 {$begin} 处理"];
        }

        $parts = explode('-', $body);
        $begin = (int) $parts[0];
        $warning = str_starts_with($parts[0], '0') && strlen($parts[0]) > 1 ? "前导 0：{$word}" : null;

        $end = $begin;
        foreach (array_slice($parts, 1) as $digits) {
            $end = self::completePrefix($end, $digits);
        }

        if ($end < $begin) {
            // 倒序（同位数）是高位笔误：para504-407 → 504-507
            $end = self::repairReversed($begin, end($parts));
            $warning = "倒序区间 {$word}，按 {$begin}-{$end} 处理";
        }

        return ['begin' => $begin, 'end' => $end, 'warning' => $warning];
    }

    /**
     * 缩写区间：右端位数少于左端时，用左端的高位补齐（292-3 → 293）。
     * 补齐后不大于左端则进一位（298-3 → 303；全库目前没有这种情况）。
     * 右端位数不少于左端时就是完整写法，原样返回。
     */
    public static function completePrefix(int $left, string $digits): int
    {
        $leftDigits = (string) $left;
        if (strlen($digits) >= strlen($leftDigits)) {
            return (int) $digits;
        }

        $completed = (int) (substr($leftDigits, 0, strlen($leftDigits) - strlen($digits)).$digits);

        return $completed > $left ? $completed : $completed + 10 ** strlen($digits);
    }

    /**
     * 倒序区间：逐位丢掉右端高位再补齐，取第一个比左端大、宽度不超过 20 的。
     */
    private static function repairReversed(int $begin, string $digits): int
    {
        for ($keep = strlen($digits) - 1; $keep >= 1; $keep--) {
            $candidate = self::completePrefix($begin, substr($digits, -$keep));
            if ($candidate > $begin && $candidate - $begin <= 20) {
                return $candidate;
            }
        }

        throw new InvalidArgumentException("倒序区间无法还原：para{$begin}-{$digits}");
    }
}
