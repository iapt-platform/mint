<?php

namespace App\Services;

/**
 * {@see AnnotationLayout} 在插入点写什么。
 *
 * 布局算法只决定「在哪里」插；「插什么」由策略给：
 * - {@see HtmlAnnotationMarkup}：直接写 HTML（默认，单元测试与旧口径）
 * - {@see Markup\Annotation\AnnotationSet}：写结构化标记的占位符，交给 App\Services\Markup 解析成模板节点，
 *   能渲染成任意格式
 */
interface AnnotationMarkup
{
    /**
     * issue 高亮开头。
     *
     * @param  array{start: int, end: int, ids: list<string>, severity: string|null}  $span
     */
    public function issueOpen(array $span): string;

    /**
     * issue 高亮结尾（与同一个 $span 的 issueOpen 配对）。
     *
     * @param  array{start: int, end: int, ids: list<string>, severity: string|null}  $span
     */
    public function issueClose(array $span): string;

    /**
     * 没有位置的 issue：句尾的三角叹号。
     *
     * @param  list<string>  $ids
     */
    public function issueMark(array $ids, ?string $severity): string;

    /**
     * 评估：句尾的对勾。
     */
    public function assessmentMark(string $id, int $score): string;
}
