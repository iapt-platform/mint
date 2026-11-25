<?php

namespace App\Services\RelatedParagraph;

/**
 * 重建结果的体检，按 book_name 汇总（documents/related-paragraphs-rebuild.md §3.1 第 9 步）。
 *
 * - 回绕：同一 book_name、同一部书（sn）内，号段从 >2 掉回 ≤2 且这个号前面已经出现过
 *   （章内局部编号换章重计，造成重号串段）
 * - 回退：其余的号段下降（义注回指，合法，只计数）
 * - 孤儿号：注释侧引用了根本侧没有的号（根本侧略去号段时是正常的）
 */
class RelatedParagraphChecker
{
    /**
     * @param  array<int, array<int, array{book_name: string, begin: int, end: int, book_id: int}>>  $results  book => 段落号 => 行
     * @param  array<int, string>  $layers  sn => 层级
     * @return array<string, array{books: array<int, array{layer: string, paragraphs: int, min: int, max: int, wraps: list<string>, backs: int, orphans: list<int>}>, mula_max: int|null}>
     */
    public function summarize(array $results, array $layers): array
    {
        $sequences = [];
        $bookLayers = [];
        foreach ($results as $book => $rows) {
            foreach ($rows as $para => $row) {
                if ($row['begin'] <= 0 || $row['book_name'] === '') {
                    continue;
                }
                $sequences[$row['book_name']][$book][$para] = [$row['begin'], $row['end'], $row['book_id']];
                $bookLayers[$row['book_name']][$book][$layers[$row['book_id']] ?? 'unknown'] = true;
            }
        }

        $summary = [];
        ksort($sequences, SORT_NATURAL);
        foreach ($sequences as $name => $books) {
            $mula = [];
            $stats = [];
            foreach ($books as $book => $paras) {
                $numbers = [];
                $wraps = [];
                $backs = 0;
                $previous = null;
                $seen = [];
                $sn = null;
                foreach ($paras as $para => [$begin, $end, $bookId]) {
                    // 一个 VRI book 里的几部书（如根本 + 自注 ṭīkā）各自从 1 编号是正常的
                    if ($bookId !== $sn) {
                        $sn = $bookId;
                        $previous = null;
                        $seen = [];
                    }
                    if ($previous !== null && $begin < $previous) {
                        if ($previous > 2 && $begin <= 2 && isset($seen[$begin])) {
                            $wraps[] = "{$previous}→{$begin}@{$para}";
                        } else {
                            $backs++;
                        }
                    }
                    foreach (range($begin, $end) as $number) {
                        $numbers[$number] = true;
                        $seen[$number] = true;
                    }
                    $previous = $begin;
                }
                $layer = implode('/', array_keys($bookLayers[$name][$book]));
                if ($layer === 'mula') {
                    $mula += $numbers;
                }
                $stats[$book] = [
                    'layer' => $layer,
                    'paragraphs' => count($paras),
                    'min' => min(array_keys($numbers)),
                    'max' => max(array_keys($numbers)),
                    'wraps' => $wraps,
                    'backs' => $backs,
                    'numbers' => $numbers,
                ];
            }

            foreach ($stats as $book => &$stat) {
                $stat['orphans'] = ($mula === [] || $stat['layer'] === 'mula')
                    ? []
                    : array_values(array_diff(array_keys($stat['numbers']), array_keys($mula)));
                unset($stat['numbers']);
            }
            unset($stat);

            $summary[$name] = ['books' => $stats, 'mula_max' => $mula === [] ? null : max(array_keys($mula))];
        }

        return $summary;
    }
}
