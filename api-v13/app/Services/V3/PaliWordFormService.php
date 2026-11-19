<?php

namespace App\Services\V3;

use App\Models\WordIndex;
use App\Tools\CaseEnding;
use App\Tools\CaseMan;

/**
 * 巴利词形展开：一个词 → 它可能的词根 → 每个词根在三藏里实际出现过的词形（含连音 iti / api / ca / hi）。
 *
 * 词根推导用共享的 `CaseMan::WordToBase(..., verify: false)`（纯字符串运算，不查库）；
 * 由词根生成词形的规则与 v2 `CaseMan::BaseToWord` 相同，但 v2 每个候选词形单独查一次
 * `word_indices`（一个词上百次），这里先生成全部候选，再一次 whereIn 取出现次数。
 */
class PaliWordFormService
{
    /** 词尾表里低于这个置信度的规则不用（与 v2 /case 相同） */
    private const CONFIDENCE = 0.2;

    public function __construct(private readonly CaseMan $caseMan) {}

    /**
     * @return list<array{base: string, forms: list<array{word: string, count: int, bold: int}>}>
     */
    public function groups(string $word): array
    {
        $word = mb_strtolower(trim($word), 'UTF-8');

        // 输入的词本身排第一组，其后是推导出的词根
        $bases = array_values(array_unique([$word, ...array_map('strval', array_keys($this->caseMan->WordToBase($word, 1, false)))]));

        $candidates = [];
        foreach ($bases as $base) {
            $candidates[$base] = $this->candidates($base);
        }

        $all = array_values(array_unique(array_merge(...array_values($candidates))));
        $counts = $all === []
            ? collect()
            : WordIndex::whereIn('word', $all)->get(['word', 'count', 'bold'])->keyBy('word');

        $groups = [];
        foreach ($candidates as $base => $words) {
            $forms = [];
            foreach ($words as $form) {
                if ($row = $counts->get($form)) {
                    $forms[] = ['word' => $form, 'count' => (int) $row->count, 'bold' => (int) $row->bold];
                }
            }
            if ($forms === []) {
                continue;
            }
            usort($forms, fn (array $a, array $b) => $b['count'] <=> $a['count'] ?: strcmp($a['word'], $b['word']));
            $groups[] = ['base' => (string) $base, 'forms' => $forms];
        }

        return $groups;
    }

    /**
     * 由词根按词尾表生成候选词形，再对每个词形套连音规则。
     *
     * @return list<string>
     */
    private function candidates(string $base): array
    {
        $table = new CaseEnding;
        $words = [];
        foreach ($table->ending as [$from, $to, , , $confidence]) {
            if ($confidence < self::CONFIDENCE || ! str_ends_with($base, $from)) {
                continue;
            }
            $word = mb_substr($base, 0, mb_strlen($base, 'UTF-8') - mb_strlen($from, 'UTF-8'), 'UTF-8').$to;
            $words[$word] = true;
            foreach ($table->union as [$end, $joined]) {
                if (str_ends_with($word, $end)) {
                    $words[mb_substr($word, 0, mb_strlen($word, 'UTF-8') - mb_strlen($end, 'UTF-8'), 'UTF-8').$joined] = true;
                }
            }
        }

        return array_map('strval', array_keys($words));
    }
}
