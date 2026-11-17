<?php

namespace App\Services\Markup\Ir;

/**
 * 一段内容的解析结果。
 *
 * - nodes：块级节点；inline 为真时整段只有一个段落，nodes 是该段落的行内节点（v2 的「单个 p 换成 span」）
 * - source：预扫描后的源码，模板位置是占位符 `\u{E000}n\u{E001}`，n 是 calls 的下标（markdown 输出用）
 * - calls：这段内容直接包含的全部模板调用（不含参数里嵌套的；那些在各自的 slot Document 里）
 * - fragment：一段行内节点的切片（如 issue 高亮包住的内容），原样渲染：html 不包 span、纯文本不按块换行
 */
final class Document
{
    /**
     * @param  list<Node>  $nodes
     * @param  array<int, Call>  $calls
     * @param  array<int, Call>  $annotationCalls  批注占位符下标 => 调用（只有点标记；markdown 输出替换源码里的占位符用）
     */
    public function __construct(
        public array $nodes,
        public bool $inline,
        public string $source,
        public array $calls,
        public Scope $scope,
        public bool $fragment = false,
        public array $annotationCalls = [],
    ) {}

    public static function empty(Scope $scope): self
    {
        return new self([], true, '', [], $scope);
    }

    public function isEmpty(): bool
    {
        return $this->nodes === [];
    }
}
