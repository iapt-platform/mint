<?php

namespace App\Services\Markup\Ir;

use App\Services\Markup\Annotation\RangeWrapper;

/**
 * 批注区间的边界（开 / 闭），只在 IR 构建期间存在：
 * {@see RangeWrapper} 把配对的边界换成包住中间内容的模板调用。
 * 配不上对的边界被丢掉，渲染器不认识它。
 */
final class Boundary implements Node
{
    public function __construct(
        public int $pair,
        public bool $open,
        public ?Call $call = null,
    ) {}
}
