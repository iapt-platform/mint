<?php

namespace App\Services\Markup\Templates;

/**
 * 模板参数声明。
 *
 * @param  int|null  $position  位置参数序号（1 起）；null 表示只能用名字传
 * @param  bool  $markdown  值是 markdown，解析时递归解析成 slot（如脚注正文）
 */
final readonly class ParamSpec
{
    public function __construct(
        public ?int $position = null,
        public string $default = '',
        public bool $markdown = false,
    ) {}

    public static function at(int $position, string $default = ''): self
    {
        return new self($position, $default);
    }

    public static function markdown(int $position): self
    {
        return new self($position, '', true);
    }
}
