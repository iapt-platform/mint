<?php

namespace App\Services\Markup\Parsing;

use App\Services\Markup\Ir\Call;
use League\CommonMark\Node\Inline\AbstractInline;

/**
 * CommonMark AST 里的模板占位节点，只记下标；IR 构建时换成 {@see Call}。
 */
final class PlaceholderNode extends AbstractInline
{
    public function __construct(public readonly int $index)
    {
        parent::__construct();
    }
}
