<?php

/**
 * wbw_templates 里 .ctl. 锚点标记的解析器。
 *
 * 这些标记来自 VRI html 的 <a name="..."> 锚点，是整个 related_paragraphs 的唯一
 * 事实来源。旧生成器（cs6_para.csv 那条链路）在这里翻过车——缩写区间被跳过或
 * 误解析成六位数废值——所以解析规则集中在这一个类里，导出器和检测器共用同一份，
 * 不允许各写一遍。
 *
 * 形态实测（type='.ctl.' and style='#a#'，330267 行）：
 *   pagemark 167647 / paraN 116921 / paraN_book 39998 / paraN-M 3611
 *   / 书名·章标记 1096 / paraN-M_book 989 / 链式 4 / 截断 1
 */
class MarkerParser
{
    /** 解析结果超过此值一律视为废值，拒绝输出。实测合法上界 3183。 */
    public const CS_MAX = 5000;

    public const KIND_SINGLE = 'single';          // para4

    public const KIND_INTERVAL = 'interval';      // para151-152 / para292-3 / para292-3-6

    public const KIND_BOOK_TAG = 'book_tag';      // dn1

    public const KIND_CHAPTER = 'chapter';        // dn1_9 / an5_1_1 —— 章标记或义注标记，同形歧义

    public const KIND_PAGE = 'page';              // P1.0023

    public const KIND_TRUNCATED = 'truncated';    // para179-

    public const KIND_UNKNOWN = 'unknown';

    /**
     * @return array{
     *   kind: string,
     *   cs: list<int>,          解析出的 cs 值（已展开区间）
     *   book_name: ?string,     标记自带的书名后缀
     *   number: ?int,           chapter 形态的首级编号
     *   numbers: ?list<int>,    chapter 形态的完整编号路径（an5_1_1 → [1,1]）
     *   repaired: ?string,      规则修补过的标记，须记 issue 复核
     *   raw: string,
     *   error: ?string,         非空表示拒绝输出，调用方须记 issue
     * }
     */
    public static function parse(string $word): array
    {
        $base = [
            'kind' => self::KIND_UNKNOWN,
            'cs' => [],
            'book_name' => null,
            'number' => null,
            'numbers' => null,
            'repaired' => null,
            'raw' => $word,
            'error' => null,
        ];

        // 页码/卷标 P/M/V/T/O × 0-5：本轮一律忽略（§6.7）
        if (preg_match('/^[A-Z][0-9]/', $word)) {
            return ['kind' => self::KIND_PAGE] + $base;
        }

        if (! str_starts_with($word, 'para')) {
            // 纯书名 dn1（书上下文事件）或 dn1_9（章标记 / 义注标记）
            // 章标记，可带多级编号路径：dn1_9 是一级，an5_1_1 是两级（实测 13 本 247 行，
            // 义注书 100-102 与 mūla 书 82-92 共用同一套编号，是跨文件章界对齐的抓手）。
            if (preg_match('/^([a-z][a-z0-9]*)((?:_[0-9]+)+)$/', $word, $m)) {
                $numbers = array_values(array_map('intval', array_filter(explode('_', $m[2]), 'strlen')));

                return [
                    'kind' => self::KIND_CHAPTER,
                    'book_name' => $m[1],
                    'number' => $numbers[0],
                    'numbers' => $numbers,
                ] + $base;
            }
            if (preg_match('/^[a-z][a-z0-9]*$/', $word)) {
                return ['kind' => self::KIND_BOOK_TAG, 'book_name' => $word] + $base;
            }

            return $base;
        }

        $body = substr($word, 4);

        // 尾部 _book 后缀。区间与单值都可能带，如 para1008-9_sn5（实测 989 行）。
        $bookName = null;
        if (preg_match('/^(.*?)_([a-z][a-z0-9_]*)$/', $body, $m)) {
            $body = $m[1];
            $bookName = $m[2];
        }

        if ($body === '' || ! preg_match('/^[0-9]/', $body)) {
            return ['error' => '标记主体不是数字'] + $base;
        }

        // para179- ：区间右端缺失，全库仅 book 11 para 1577 一处，留给人工。
        if (str_ends_with($body, '-')) {
            return ['kind' => self::KIND_TRUNCATED, 'book_name' => $bookName, 'error' => '区间右端缺失'] + $base;
        }

        $parts = explode('-', $body);
        foreach ($parts as $part) {
            if (! preg_match('/^[0-9]+$/', $part)) {
                return ['error' => "区间片段非数字：$part"] + $base;
            }
        }

        if (count($parts) === 1) {
            $cs = (int) $parts[0];
            if ($cs > self::CS_MAX) {
                return ['book_name' => $bookName, 'error' => "cs 超值域：$cs"] + $base;
            }

            return ['kind' => self::KIND_SINGLE, 'cs' => [$cs], 'book_name' => $bookName] + $base;
        }

        // 链式 para292-3-6：逐段以前一个完整值补齐，取首尾展开（实测仅 4 行）。
        $start = (int) $parts[0];
        $current = $start;
        for ($i = 1; $i < count($parts); $i++) {
            $current = self::completePrefix($current, $parts[$i]);
            if ($current === null) {
                return ['book_name' => $bookName, 'error' => "区间右端无法补齐：$body"] + $base;
            }
        }
        $end = $current;

        $repaired = null;
        if ($end < $start) {
            // 倒序区间是 VRI 源里的高位笔误，低位是对的：实测 para504-407 前邻 502-503
            // 后邻 508（真值 504-507）、para706-608 前邻 703-705 后邻 709（真值 706-708）。
            // 丢掉右端高位、只用低位重新补齐即可还原。全库仅 3 行，一律记 issue 人工复核。
            $tail = end($parts);
            $end = self::repairReversedEnd($start, $tail);
            if ($end === null) {
                return ['book_name' => $bookName, 'error' => "区间倒序且无法修补：$body"] + $base;
            }
            $repaired = "倒序区间按低位补齐：$body → $start-$end";
        }
        if ($end > self::CS_MAX || $start > self::CS_MAX) {
            return ['book_name' => $bookName, 'error' => "cs 超值域：$start-$end"] + $base;
        }
        if ($end - $start > 2000) {
            return ['book_name' => $bookName, 'error' => "区间过宽：$start-$end"] + $base;
        }

        return [
            'kind' => self::KIND_INTERVAL,
            'cs' => range($start, $end),
            'book_name' => $bookName,
            'repaired' => $repaired,
        ] + $base;
    }

    /**
     * VRI 缩写区间语法：右端只写变化的低位，用左端补齐前缀。
     *   292-3   → 293      （补 "29"）
     *   1008-9  → 1009     （补 "100"）
     *   15-8    → 18       （补 "1"）
     *   298-3   → 303      （补出来 293 < 298，进位加 10^1）
     * 右端位数不少于左端时不是缩写，原样返回。
     */
    public static function completePrefix(int $left, string $rightDigits): ?int
    {
        $leftDigits = (string) $left;
        $rightLen = strlen($rightDigits);

        if ($rightLen >= strlen($leftDigits)) {
            return (int) $rightDigits;
        }

        $prefix = substr($leftDigits, 0, strlen($leftDigits) - $rightLen);
        $completed = (int) ($prefix.$rightDigits);

        if ($completed <= $left) {
            // 低位回绕，向上进一位：298-3 的 3 指的是 303 不是 293。
            $completed += 10 ** $rightLen;
        }

        return $completed > $left ? $completed : null;
    }

    /**
     * 倒序区间的还原：逐步丢弃右端高位，只保留低位再按前缀补齐，取第一个大于左端
     * 且区间宽度合理的结果。
     *   504-407 → 低两位 "07" 补成 507
     *   706-608 → 低两位 "08" 补成 708
     */
    public static function repairReversedEnd(int $start, string $rightDigits): ?int
    {
        for ($keep = strlen($rightDigits) - 1; $keep >= 1; $keep--) {
            $candidate = self::completePrefix($start, substr($rightDigits, -$keep));
            if ($candidate !== null && $candidate > $start && $candidate - $start <= 20) {
                return $candidate;
            }
        }

        return null;
    }
}
