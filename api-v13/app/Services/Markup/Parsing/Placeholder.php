<?php

namespace App\Services\Markup\Parsing;

/**
 * 预扫描用的占位符：私用区字符包住模板调用的下标，`\u{E000}12\u{E001}`。
 *
 * CommonMark 把它当普通文字，所以模板里的 `|`、空行、列表符号都不会打断段落或表格；
 * 解析完再由 {@see PlaceholderParser} 换回模板节点。
 */
final class Placeholder
{
    public const OPEN = "\u{E000}";

    public const CLOSE = "\u{E001}";

    public const PATTERN = '/'."\u{E000}".'(\d+)'."\u{E001}".'/u';

    public static function make(int $index): string
    {
        return self::OPEN.$index.self::CLOSE;
    }

    /**
     * 去掉源码里本来就有的占位符字符，免得与真占位符混淆。
     */
    public static function strip(string $source): string
    {
        return str_replace([self::OPEN, self::CLOSE], '', $source);
    }

    /**
     * 把文字里的占位符换回源码原文（代码块、原始 HTML 属性等不该展开模板的地方用）。
     *
     * @param  array<int, string>  $raws  下标 => 原文
     */
    public static function restore(string $text, array $raws): string
    {
        return (string) preg_replace_callback(
            self::PATTERN,
            fn (array $m) => $raws[(int) $m[1]] ?? '',
            $text,
        );
    }
}
