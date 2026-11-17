<?php

namespace App\Services;

/**
 * 批注标记的 HTML 写法：阅读器（library、wikipali-mobile）的 CSS 与点击处理都认这套 class / data-*。
 *
 * {@see AnnotationLayout} 的默认策略；App\Services\Markup 的批注模板（anno-*）输出 html 时也用这里的方法，
 * 两条路径的 HTML 逐字节一致。
 */
class HtmlAnnotationMarkup implements AnnotationMarkup
{
    public function issueOpen(array $span): string
    {
        return self::issueOpenTag($span['ids'], $span['severity']);
    }

    public function issueClose(array $span): string
    {
        return '</span>';
    }

    public function issueMark(array $ids, ?string $severity): string
    {
        return '<span class="anno-issue-mark anno-issue-'.($severity ?? 'none').'"'
            .' data-discussion-ids="'.e(implode(',', $ids)).'"'
            .' data-severity="'.e($severity ?? '').'" role="button" tabindex="0">'
            .'<i class="ti ti-alert-triangle"></i></span>';
    }

    public function assessmentMark(string $id, int $score): string
    {
        $level = AnnotationLayout::assessmentLevel($score);

        return '<span class="anno-assessment-mark anno-assessment-'.$level.'"'
            .' data-discussion-ids="'.e($id).'"'
            .' data-score="'.$score.'" data-level="'.$level.'" role="button" tabindex="0">'
            .'<i class="ti ti-circle-check"></i></span>';
    }

    /**
     * 没有评估的句子：灰色对勾，不能点（没有 discussion id）。
     */
    public static function unassessedMark(): string
    {
        return '<span class="anno-assessment-mark anno-assessment-none" data-level="none" aria-hidden="true">'
            .'<i class="ti ti-circle-check"></i></span>';
    }

    /**
     * @param  list<string>  $ids
     */
    public static function issueOpenTag(array $ids, ?string $severity): string
    {
        return '<span class="anno-issue anno-issue-'.($severity ?? 'none').'"'
            .' data-discussion-ids="'.e(implode(',', $ids)).'"'
            .' data-severity="'.e($severity ?? '').'">';
    }
}
