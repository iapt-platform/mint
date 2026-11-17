<?php

namespace App\Services\Markup\Annotation;

use App\Services\Markup\Parsing\Placeholder;

/**
 * 批注标记在源码里的占位符：`\u{E002}n\u{E003}`，n 是 {@see AnnotationSet} 里的下标。
 *
 * 与模板占位符（{@see Placeholder}，E000 / E001）用不同的字符，互不干扰。
 */
final class AnnotationToken
{
    public const OPEN = "\u{E002}";

    public const CLOSE = "\u{E003}";

    public const PATTERN = '/'."\u{E002}".'(\d+)'."\u{E003}".'/u';

    public static function make(int $index): string
    {
        return self::OPEN.$index.self::CLOSE;
    }

    /**
     * 去掉占位符（代码里、属性里等不该出现批注的地方），以及源码里本来就有的这两个字符。
     */
    public static function strip(string $text): string
    {
        return str_replace([self::OPEN, self::CLOSE], '', (string) preg_replace(self::PATTERN, '', $text));
    }
}
