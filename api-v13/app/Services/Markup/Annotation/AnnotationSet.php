<?php

namespace App\Services\Markup\Annotation;

use App\Services\AnnotationLayout;
use App\Services\AnnotationMarkup;

/**
 * 结构化的批注注入：作为 {@see AnnotationLayout} 的输出策略，在插入点写占位符而不是 HTML，
 * 同时记下每个占位符的结构化内容（{@see Marker}）。解析时占位符变成模板节点
 * （anno-issue、anno-issue-mark、anno-assessment、note），能渲染成任意格式。
 *
 * ```php
 * $set = new AnnotationSet;
 * $layout = AnnotationLayout::apply($content, $glossaries, $issues, $notes, true, $assessment, $set);
 * $markup->renderMany([['source' => $layout['content'], 'annotations' => $set, …]], $options);
 * ```
 *
 * 一个实例只对应一段内容（下标在这段内容里唯一）。
 */
final class AnnotationSet implements AnnotationMarkup
{
    /** @var list<Marker> */
    private array $markers = [];

    /** @var array<string, int> issue 区间 → pair 编号 */
    private array $pairs = [];

    public function issueOpen(array $span): string
    {
        $pair = count($this->pairs) + 1;
        $this->pairs[self::spanKey($span)] = $pair;

        return $this->add(new Marker(Marker::OPEN, $pair, 'anno-issue', [
            'ids' => implode(',', $span['ids']),
            'severity' => (string) ($span['severity'] ?? ''),
        ]));
    }

    public function issueClose(array $span): string
    {
        return $this->add(new Marker(Marker::CLOSE, $this->pairs[self::spanKey($span)] ?? 0));
    }

    public function issueMark(array $ids, ?string $severity): string
    {
        return $this->add(new Marker(Marker::POINT, 0, 'anno-issue-mark', [
            'ids' => implode(',', $ids),
            'severity' => (string) ($severity ?? ''),
        ]));
    }

    public function assessmentMark(string $id, int $score): string
    {
        return $this->add(new Marker(Marker::POINT, 0, 'anno-assessment', ['id' => $id, 'score' => (string) $score]));
    }

    /**
     * 没有评估的句子的灰色对勾（同段有别的句子评估过时补上）。
     */
    public function unassessedMark(): string
    {
        return $this->add(new Marker(Marker::POINT, 0, 'anno-assessment'));
    }

    /**
     * 边注 / 义注对应：一个 note 模板，正文是 markdown（解析成节点，格式与模板都保留）。
     *
     * @param  array<string, string>  $params  trigger / cite / citelink
     * @param  string  $contentType  正文的方言（取自批注那一行）
     */
    public function note(string $body, array $params = [], string $contentType = 'markdown'): string
    {
        return $this->add(new Marker(Marker::POINT, 0, 'note', $params, $body, $contentType));
    }

    public function get(int $index): ?Marker
    {
        return $this->markers[$index] ?? null;
    }

    private function add(Marker $marker): string
    {
        $this->markers[] = $marker;

        return AnnotationToken::make(count($this->markers) - 1);
    }

    /**
     * @param  array{start: int, end: int}  $span
     */
    private static function spanKey(array $span): string
    {
        return $span['start'].'-'.$span['end'];
    }
}
