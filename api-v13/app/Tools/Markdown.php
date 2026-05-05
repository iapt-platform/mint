<?php

namespace App\Tools;

use Illuminate\Support\Str;

class Markdown
{
    public static function driver($driver)
    {
        $GLOBALS['markdown.driver'] = $driver;
    }
    public static function render($text)
    {
        return Markdown::strMarkdown($text);
    }


    public static function strMarkdown($text)
    {
        $text = str_replace("** ", "**\r\n ", $text);
        $html = Str::markdown($text);
        $html = self::replaceSinglePWithSpan($html);
        return $html;
    }
    /**
     * 如果字符串中只有一对 p 标签，则替换为 span
     *
     * @param string $html
     * @return string
     */
    public static function replaceSinglePWithSpan(string $html): string
    {
        // 先过滤掉只含空白/全角空格的 <p>
        $html = preg_replace('/<p\b[^>]*>[\s　]*<\/p>/u', '', $html);
        $html = trim($html);

        preg_match_all('/<p\b[^>]*>.*?<\/p>/is', $html, $matches);

        if (count($matches[0]) === 1) {
            return preg_replace(
                ['/^\s*<p\b([^>]*)>/i', '/<\/p>\s*$/i'],
                ['<span$1>', '</span>'],
                $html
            );
        }

        return $html;
    }
}
