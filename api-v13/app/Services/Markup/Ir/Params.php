<?php

namespace App\Services\Markup\Ir;

/**
 * 模板参数表。
 *
 * 命名参数 `name=value` 与位置参数并存；位置序号按全部参数的顺序计（命名参数也占位），
 * 与 v2 一致：`{{note|text=a|①}}` 里 ① 是第 2 个参数。
 */
final class Params
{
    /**
     * @param  array<string, string>  $named
     * @param  array<int, string>  $positional  1 起
     */
    public function __construct(
        public array $named = [],
        public array $positional = [],
    ) {}

    public function get(string $name, ?int $position, ?string $default = ''): ?string
    {
        if (array_key_exists($name, $this->named)) {
            return trim($this->named[$name]);
        }
        if ($position !== null && array_key_exists($position, $this->positional)) {
            return trim($this->positional[$position]);
        }

        return $default;
    }

    /**
     * 原值（不 trim），给 markdown 参数用。
     */
    public function raw(string $name, ?int $position): ?string
    {
        if (array_key_exists($name, $this->named)) {
            return $this->named[$name];
        }
        if ($position !== null && array_key_exists($position, $this->positional)) {
            return $this->positional[$position];
        }

        return null;
    }

    public function with(string $name, string $value): self
    {
        $copy = clone $this;
        $copy->named[$name] = $value;

        return $copy;
    }

    /**
     * 全部参数，命名的用名字、位置的用序号字符串作键（Tpl 模板交给 Mustache 用）。
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $all = [];
        foreach ($this->positional as $position => $value) {
            $all[(string) $position] = trim($value);
        }
        foreach ($this->named as $name => $value) {
            $all[$name] = trim($value);
        }

        return $all;
    }
}
