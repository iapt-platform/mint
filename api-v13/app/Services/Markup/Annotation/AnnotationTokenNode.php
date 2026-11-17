<?php

namespace App\Services\Markup\Annotation;

use League\CommonMark\Node\Inline\AbstractInline;

/**
 * CommonMark AST 里的批注占位节点，只记下标；IR 构建时换成模板调用或区间边界。
 */
final class AnnotationTokenNode extends AbstractInline
{
    public function __construct(public readonly int $index)
    {
        parent::__construct();
    }
}
