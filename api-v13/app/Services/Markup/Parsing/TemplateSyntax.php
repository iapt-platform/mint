<?php

namespace App\Services\Markup\Parsing;

/**
 * `{{name|a|b=c}}` 的切分：模板名 + 参数片段。只在顶层（不在嵌套的 `{{ }}` / `[[ ]]` 里）切 `|`。
 */
final class TemplateSyntax
{
    /**
     * @return array{0: string, 1: list<string>} [模板名, 参数片段]
     */
    public static function split(string $inner): array
    {
        $segments = self::splitTopLevel($inner);
        $name = trim(array_shift($segments) ?? '');

        // v2：`|` 后紧跟的换行去掉（`{{note|\n内容\n}}` 的内容不以换行开头）
        $segments = array_map(
            fn (string $segment) => (string) preg_replace('/^\r?\n/', '', $segment),
            $segments,
        );

        return [$name, $segments];
    }

    /**
     * @return list<string>
     */
    private static function splitTopLevel(string $text): array
    {
        $parts = [];
        $current = '';
        $braces = 0;
        $brackets = 0;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $pair = substr($text, $i, 2);
            if ($pair === '{{' || $pair === '}}' || $pair === '[[' || $pair === ']]') {
                match ($pair) {
                    '{{' => $braces++,
                    '}}' => $braces = max(0, $braces - 1),
                    '[[' => $brackets++,
                    ']]' => $brackets = max(0, $brackets - 1),
                };
                $current .= $pair;
                $i++;

                continue;
            }
            $char = $text[$i];
            if ($char === '|' && $braces === 0 && $brackets === 0) {
                $parts[] = $current;
                $current = '';

                continue;
            }
            $current .= $char;
        }
        $parts[] = $current;

        return $parts;
    }
}
