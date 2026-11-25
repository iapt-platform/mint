<?php

namespace App\Services\RelatedParagraph;

/**
 * 一个 VRI book 的「段落 → (book_name, begin, end)」重建，纯计算，不碰数据库。
 *
 * 规则见 documents/related-paragraphs-rebuild.md §3.1：
 *   - 每段最多一个号段，锚点在段首；无锚点的段继承上一个号段
 *   - 遇 book_titles.sn 边界、或书名变化（书名标记 / 序言进出）即停止继承——宁空勿错
 *   - 书名：锚点后缀 > sn 书名表 > 文件内书名 / 章标记 > 空；序言（`X_0` 之后）里的
 *     `paraN_X` 仍归 `X_A`
 *   - 补丁表的段落用补丁值覆盖（book_name 为空表示沿用重建出的名字）
 *   - 最后仍没有名字的号清成 0：空名也是一个名字，`'' + 5` 会把互不相干的书配在一起
 */
class ParagraphMapBuilder
{
    public const SOURCE_ANCHOR = 'anchor';

    public const SOURCE_INHERIT = 'inherit';

    public const SOURCE_PATCH = 'patch';

    public const SOURCE_NONE = 'none';

    /**
     * @param  list<int>  $paragraphs  pali_texts 的段落号（升序），段落全集
     * @param  array<int, list<string>>  $markers  段落号 => 该段的 .ctl. 锚点原词（按 wid）
     * @param  array<int, int>  $titles  level-1 起始段落号 => sn（升序）
     * @param  array<int, string>  $snNames  sn => book_name（sn 书名表）
     * @param  array<int, array{book_name: string|null, begin: int, end: int}>  $patch  段落号 => 补丁值
     * @return array{rows: array<int, array{book_name: string, begin: int, end: int, book_id: int, source: string, cleared: bool}>, warnings: list<string>}
     */
    public function build(array $paragraphs, array $markers, array $titles, array $snNames, array $patch = []): array
    {
        ksort($titles);
        $rows = [];
        $warnings = [];
        $section = null;
        $current = null;

        foreach ($paragraphs as $para) {
            $sn = $this->snAt($titles, $para);
            if ($current !== null && $current['sn'] !== $sn) {
                $current = null;
            }

            $anchor = null;
            foreach ($markers[$para] ?? [] as $word) {
                $parsed = AnchorParser::parse($word);
                if (! empty($parsed['warning'])) {
                    $warnings[] = "段落 {$para}：{$parsed['warning']}";
                }

                if ($parsed['kind'] === AnchorParser::KIND_BOOK || $parsed['kind'] === AnchorParser::KIND_CHAPTER) {
                    $next = ! empty($parsed['preface']) ? $parsed['name'].'_A' : $parsed['name'];
                    if ($next !== $section) {
                        $section = $next;
                        $current = null;
                    }
                } elseif ($parsed['kind'] === AnchorParser::KIND_PARA) {
                    // 成对出现的 paraN / paraN_dn1：号段相同，有后缀的那个决定名字
                    if ($anchor === null || $parsed['suffix'] !== null) {
                        $anchor = $parsed;
                    }
                }
            }

            if ($anchor !== null) {
                $current = [
                    'book_name' => $this->resolveName($anchor['suffix'], $snNames[$sn] ?? null, $section),
                    'begin' => $anchor['begin'],
                    'end' => $anchor['end'],
                    'sn' => $sn,
                ];
                $row = ['book_name' => $current['book_name'], 'begin' => $current['begin'], 'end' => $current['end'], 'source' => self::SOURCE_ANCHOR];
            } elseif ($current !== null) {
                $row = ['book_name' => $current['book_name'], 'begin' => $current['begin'], 'end' => $current['end'], 'source' => self::SOURCE_INHERIT];
            } else {
                $row = ['book_name' => $snNames[$sn] ?? $section ?? '', 'begin' => 0, 'end' => 0, 'source' => self::SOURCE_NONE];
            }

            if (isset($patch[$para])) {
                $fix = $patch[$para];
                $row = [
                    'book_name' => $fix['book_name'] ?? $row['book_name'],
                    'begin' => $fix['begin'],
                    'end' => $fix['end'],
                    'source' => self::SOURCE_PATCH,
                ];
            }

            $row['cleared'] = false;
            if ($row['book_name'] === '' && $row['begin'] > 0) {
                $row['begin'] = 0;
                $row['end'] = 0;
                $row['cleared'] = true;
            }
            $row['book_id'] = $sn;
            $rows[$para] = $row;
        }

        return ['rows' => $rows, 'warnings' => $warnings];
    }

    /**
     * 段落所在的 sn：起始段落号不大于它的最后一个 level-1；在第一个 level-1 之前为 0（书前页）。
     *
     * @param  array<int, int>  $titles
     */
    private function snAt(array $titles, int $para): int
    {
        $sn = 0;
        foreach ($titles as $from => $titleSn) {
            if ($from > $para) {
                break;
            }
            $sn = $titleSn;
        }

        return $sn;
    }

    private function resolveName(?string $suffix, ?string $snName, ?string $section): string
    {
        if ($suffix !== null) {
            // 序言里的 paraN_X 只给号，不夺名
            return $section === $suffix.'_A' ? $section : $suffix;
        }

        return $snName ?? $section ?? '';
    }
}
