<?php

namespace App\Services;

/**
 * 阅读页批注排版：把锚定在一句译文上的几种批注一次性排进句子的原始 content（markdown）。
 *
 * 纯计算，不查库、不渲染——输入 content 与已解析好的批注，输出改写后的 content。
 * 调用方是 {@see PaliContentService::injectAnnotationNotes()}，改写后的 content 再交给 MdRender。
 *
 * 三种东西，排法各不相同：
 * - glossary：区间 [start, end) 的原文换成 `[[title#原文]]`，MdRender 渲染成术语；
 * - issue：区间外包一层 `<span class="anno-issue …" data-discussion-ids="…">`（高亮，点击看内容）；
 *   没有位置的 issue 合成一个三角叹号图标，放在句尾；
 * - note / commentary：在插入点插入调用方预先渲染好的 `{{note|…}}` 模板。
 *
 * 交叠的处理（核心约束：任何时候都不能切断一个标记）：
 * - 受保护区间：content 里本来就有的标记（`[[…]]`、`{{…}}`、`` `…` ``、`**…**`、HTML 标签），
 *   谁都不能切断；
 * - glossary 碰到受保护区间就放弃；两个 glossary 交叠时，包含关系留外层，交叉关系留靠前的，
 *   另一条放弃（`[[…]]` 不能嵌套，也不能交叉）；
 * - issue 与 glossary / 受保护区间部分交叠时，把 issue 扩展到完整包住对方；
 *   issue 之间交叠或包含就合并成一个 span，id 取并集、severity 取最重的；
 *   扩展可能引出新的交叠，反复处理直到稳定。仅仅首尾相接的不合并；
 * - note 的插入点落在 glossary 或受保护区间内部时，挪到该区间的末尾；落在 issue 内部则原地插入
 *   （行内元素嵌套是合法的）；
 * - 同一位置上的输出顺序：先收尾的 issue `</span>`，再 note，再开头的 issue `<span>`——
 *   note 锚定的是位置之前的文字，应当跟在结束的高亮之后、开始的高亮之前。
 */
class AnnotationLayout
{
    /** 严重程度由轻到重；合并时取最重的，没有 severity 的最轻 */
    private const SEVERITY_RANK = ['suggestion' => 1, 'warning' => 2, 'error' => 3];

    /** glossary 的原文或词头里不能有这些字符，否则 `[[title#原文]]` 会被拆坏 */
    private const GLOSSARY_FORBIDDEN = '/[\[\]{}|#\r\n]/u';

    /**
     * 受保护区间：已有的模板、术语、行内注释、粗体与 HTML 标签。
     */
    private const PROTECTED_PATTERN = '/\[\[.*?\]\]|\{\{.*?\}\}|`[^`]*`|\*\*.+?\*\*|<[^<>]+>/us';

    /**
     * 按选择器在 content 里定位一段文字，返回 [start, end)（字符偏移），定位不到返回 null。
     *
     * 先信位置：pos 合法且（没有摘录或摘录对得上）就用 pos。对不上说明正文改过，按摘录重新找：
     * 有前后缀就优先选前后缀也吻合的那一处，同分取离原位置最近的。
     *
     * @return array{0: int, 1: int}|null
     */
    public static function locate(
        string $content,
        ?int $start,
        ?int $end,
        ?string $exact = null,
        ?string $prefix = null,
        ?string $suffix = null,
    ): ?array {
        $len = mb_strlen($content, 'UTF-8');
        $exact = (string) $exact;
        $posValid = $start !== null && $end !== null && $start >= 0 && $start < $end && $end <= $len;
        if ($posValid && ($exact === '' || mb_substr($content, $start, $end - $start, 'UTF-8') === $exact)) {
            return [$start, $end];
        }
        if ($exact === '') {
            return null;
        }

        $exactLen = mb_strlen($exact, 'UTF-8');
        $prefix = (string) $prefix;
        $suffix = (string) $suffix;
        $best = null;
        $bestScore = null;
        $offset = 0;
        while (($found = mb_strpos($content, $exact, $offset, 'UTF-8')) !== false) {
            // 前缀：紧挨在前面的文字是前缀的结尾（句首附近可能不够长，比能比的那部分）
            $n = min($found, mb_strlen($prefix, 'UTF-8'));
            $before = mb_substr($content, $found - $n, $n, 'UTF-8');
            $matchPrefix = $n > 0 && str_ends_with($prefix, $before);
            // 后缀：紧跟在后面的文字是后缀的开头
            $after = mb_substr($content, $found + $exactLen, mb_strlen($suffix, 'UTF-8'), 'UTF-8');
            $matchSuffix = $after !== '' && str_starts_with($suffix, $after);
            $distance = $start === null ? $found : abs($found - $start);
            // 分数：前后缀吻合得越多越好，其次离原位置越近越好
            $score = [(int) $matchPrefix + (int) $matchSuffix, -$distance];
            if ($bestScore === null || $score > $bestScore) {
                $best = $found;
                $bestScore = $score;
            }
            $offset = $found + 1;
        }

        return $best === null ? null : [$best, $best + $exactLen];
    }

    /**
     * 排版。
     *
     * @param  list<array{id: string, start: int, end: int, title: string}>  $glossaries  已定位的术语
     * @param  list<array{id: string, start: int|null, end: int|null, severity: string|null}>  $issues  start/end 为 null 表示没有位置
     * @param  list<array{id: string, at: int, order: array<int, int>, html: string}>  $notes  at 是插入点，order 是同一插入点上的先后
     * @param  bool  $withIssues  是否输出 issue 的高亮与图标（只有 html 格式需要）
     * @return array{content: string, notes: list<string>, glossaries: list<string>, issues: list<array{ids: list<string>, severity: string|null, start: int|null, end: int|null}>}
     *
     * 返回值里 notes 是 note id 的阅读顺序，glossaries 是实际替换了的术语 id，
     * issues 是合并后的高亮（没有位置的合成一条，start / end 为 null）。
     */
    public static function apply(string $content, array $glossaries, array $issues, array $notes, bool $withIssues = true): array
    {
        $len = mb_strlen($content, 'UTF-8');
        $protected = self::protectedRanges($content);

        // ---- glossary：放弃碰到受保护区间的、字符不合法的，然后贪心取不交叠的
        $glossaries = array_values(array_filter($glossaries, function (array $g) use ($content, $len, $protected) {
            if ($g['start'] < 0 || $g['start'] >= $g['end'] || $g['end'] > $len) {
                return false;
            }
            $text = mb_substr($content, $g['start'], $g['end'] - $g['start'], 'UTF-8');
            if (trim($g['title']) === '' || preg_match(self::GLOSSARY_FORBIDDEN, $text.$g['title'])) {
                return false;
            }
            foreach ($protected as $p) {
                if (self::intersects($g, $p)) {
                    return false;
                }
            }

            return true;
        }));
        // 开头靠前的优先；开头相同的，长的（外层）优先
        usort($glossaries, fn ($a, $b) => [$a['start'], $b['end'], $a['id']] <=> [$b['start'], $a['end'], $b['id']]);
        $kept = [];
        foreach ($glossaries as $g) {
            foreach ($kept as $k) {
                if (self::intersects($g, $k)) {
                    continue 2;
                }
            }
            $kept[] = $g;
        }
        $glossaries = $kept;

        // ---- issue：扩展到不切断任何 glossary / 受保护区间，再合并交叠的，直到稳定
        $spans = [];
        $unanchored = [];
        foreach ($issues as $issue) {
            $s = $issue['start'];
            $e = $issue['end'];
            if ($s === null || $e === null || $s < 0 || $s >= $e || $e > $len) {
                $unanchored[] = $issue;

                continue;
            }
            $spans[] = ['start' => $s, 'end' => $e, 'ids' => [$issue['id']], 'severity' => $issue['severity']];
        }
        $spans = self::settleSpans($spans, [...$glossaries, ...$protected]);

        // ---- note：插入点不能落在 glossary / 受保护区间内部
        $blockers = [...$glossaries, ...$protected];
        foreach ($notes as &$note) {
            $at = min(max($note['at'], 0), $len);
            do {
                $moved = false;
                foreach ($blockers as $b) {
                    if ($b['start'] < $at && $at < $b['end']) {
                        $at = $b['end'];
                        $moved = true;
                    }
                }
            } while ($moved);
            $note['at'] = $at;
        }
        unset($note);
        usort($notes, fn ($a, $b) => [$a['at'], ...$a['order']] <=> [$b['at'], ...$b['order']]);

        // ---- 按位置从左到右输出
        $opens = [];
        $closes = [];
        if ($withIssues) {
            foreach ($spans as $span) {
                $opens[$span['start']][] = $span;
                $closes[$span['end']][] = $span;
            }
        }
        $notesAt = [];
        foreach ($notes as $note) {
            $notesAt[$note['at']][] = $note['html'];
        }
        $glossaryAt = [];
        foreach ($glossaries as $g) {
            $glossaryAt[$g['start']] = $g;
        }
        $points = array_unique([0, $len, ...array_keys($opens), ...array_keys($closes), ...array_keys($notesAt), ...array_keys($glossaryAt)]);
        sort($points);

        $out = '';
        $cursor = 0;
        foreach ($points as $x) {
            if ($x < $cursor) {
                continue;   // 落在已整段输出的 glossary 内部：构造上不会出现，只是防御
            }
            $out .= mb_substr($content, $cursor, $x - $cursor, 'UTF-8');
            $cursor = $x;
            // 合并后 issue 之间互不交叠，同一点上至多一个收尾、一个开头
            foreach ($closes[$x] ?? [] as $span) {
                $out .= '</span>';
            }
            foreach ($notesAt[$x] ?? [] as $html) {
                $out .= $html;
            }
            foreach ($opens[$x] ?? [] as $span) {
                $out .= self::issueOpenTag($span['ids'], $span['severity']);
            }
            if (isset($glossaryAt[$x])) {
                $g = $glossaryAt[$x];
                $text = mb_substr($content, $g['start'], $g['end'] - $g['start'], 'UTF-8');
                $out .= '[['.trim($g['title']).'#'.$text.']]';
                $cursor = $g['end'];
            }
        }
        $out .= mb_substr($content, $cursor, null, 'UTF-8');

        if ($withIssues && $unanchored !== []) {
            $out .= self::issueMark(
                array_column($unanchored, 'id'),
                self::maxSeverity(array_column($unanchored, 'severity')),
            );
        }

        $report = array_map(fn ($s) => [
            'ids' => $s['ids'], 'severity' => $s['severity'], 'start' => $s['start'], 'end' => $s['end'],
        ], $spans);
        if ($unanchored !== []) {
            $report[] = [
                'ids' => array_column($unanchored, 'id'),
                'severity' => self::maxSeverity(array_column($unanchored, 'severity')),
                'start' => null,
                'end' => null,
            ];
        }

        return [
            'content' => $out,
            'notes' => array_column($notes, 'id'),
            'glossaries' => array_column($glossaries, 'id'),
            'issues' => $report,
        ];
    }

    /**
     * content 里已有的标记占据的区间（字符偏移）。
     *
     * @return list<array{start: int, end: int}>
     */
    public static function protectedRanges(string $content): array
    {
        preg_match_all(self::PROTECTED_PATTERN, $content, $matches, PREG_OFFSET_CAPTURE);
        $ranges = [];
        foreach ($matches[0] as [$text, $byteOffset]) {
            $start = mb_strlen(substr($content, 0, $byteOffset), 'UTF-8');
            $ranges[] = ['start' => $start, 'end' => $start + mb_strlen($text, 'UTF-8')];
        }

        return $ranges;
    }

    /**
     * 把 issue 区间扩展到不切断任何障碍物，并合并交叠的 issue，反复直到稳定。
     *
     * @param  list<array{start: int, end: int, ids: list<string>, severity: string|null}>  $spans
     * @param  list<array{start: int, end: int}>  $blockers
     * @return list<array{start: int, end: int, ids: list<string>, severity: string|null}>
     */
    private static function settleSpans(array $spans, array $blockers): array
    {
        do {
            $changed = false;
            // 扩展：与障碍物交叠但没有完整包住它，就把它包进来
            foreach ($spans as &$span) {
                foreach ($blockers as $b) {
                    if (self::intersects($span, $b) && ($b['start'] < $span['start'] || $b['end'] > $span['end'])) {
                        $span['start'] = min($span['start'], $b['start']);
                        $span['end'] = max($span['end'], $b['end']);
                        $changed = true;
                    }
                }
            }
            unset($span);
            // 合并：按开头排序后，与上一个交叠（含包含）的并进去
            usort($spans, fn ($a, $b) => [$a['start'], $a['end']] <=> [$b['start'], $b['end']]);
            $merged = [];
            foreach ($spans as $span) {
                $last = count($merged) - 1;
                if ($last >= 0 && self::intersects($merged[$last], $span)) {
                    $merged[$last]['end'] = max($merged[$last]['end'], $span['end']);
                    $merged[$last]['ids'] = [...$merged[$last]['ids'], ...$span['ids']];
                    $merged[$last]['severity'] = self::maxSeverity([$merged[$last]['severity'], $span['severity']]);
                    $changed = true;
                } else {
                    $merged[] = $span;
                }
            }
            $spans = $merged;
        } while ($changed);

        return $spans;
    }

    /**
     * 两个半开区间是否有公共部分（首尾相接不算）。
     *
     * @param  array{start: int, end: int}  $a
     * @param  array{start: int, end: int}  $b
     */
    private static function intersects(array $a, array $b): bool
    {
        return $a['start'] < $b['end'] && $b['start'] < $a['end'];
    }

    /**
     * @param  array<int, string|null>  $severities
     */
    private static function maxSeverity(array $severities): ?string
    {
        $max = null;
        foreach ($severities as $severity) {
            if ((self::SEVERITY_RANK[$severity] ?? 0) > (self::SEVERITY_RANK[$max] ?? 0)) {
                $max = $severity;
            }
        }

        return $max;
    }

    /**
     * @param  list<string>  $ids
     */
    private static function issueOpenTag(array $ids, ?string $severity): string
    {
        return '<span class="anno-issue anno-issue-'.($severity ?? 'none').'"'
            .' data-discussion-ids="'.e(implode(',', $ids)).'"'
            .' data-severity="'.e($severity ?? '').'">';
    }

    /**
     * 没有位置的 issue：句尾一个三角叹号图标，颜色取最重的 severity。
     *
     * @param  list<string>  $ids
     */
    private static function issueMark(array $ids, ?string $severity): string
    {
        return '<span class="anno-issue-mark anno-issue-'.($severity ?? 'none').'"'
            .' data-discussion-ids="'.e(implode(',', $ids)).'"'
            .' data-severity="'.e($severity ?? '').'" role="button" tabindex="0">'
            .'<i class="ti ti-alert-triangle"></i></span>';
    }
}
