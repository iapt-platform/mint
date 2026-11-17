<?php

namespace App\Services\Markup\Ir;

/**
 * 纯文本（未转义）。
 */
final class Text implements Node
{
    public function __construct(public string $value) {}
}
