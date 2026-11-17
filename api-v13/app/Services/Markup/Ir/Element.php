<?php

namespace App\Services\Markup\Ir;

use App\Services\Markup\Html\HtmlPolicy;

/**
 * 普通 HTML 元素。tag 与属性都已经过 {@see HtmlPolicy} 白名单。
 */
final class Element implements Node
{
    /**
     * @param  array<string, string>  $attrs
     * @param  list<Node>  $children
     */
    public function __construct(
        public string $tag,
        public array $attrs = [],
        public array $children = [],
    ) {}
}
