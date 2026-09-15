<?php

// app/Support/WikiContentParser.php

namespace App\Helpers;

class WikiContentParser
{
    /**
     * 给 HTML 中的 h1~h3 注入 id，并提取目录结构
     * 返回 ['content' => string, 'toc' => array]
     */
    public static function parse(string $html): array
    {
        $toc = [];
        $slugCount = [];

        $content = preg_replace_callback(
            '/<(h[123])([^>]*)>(.*?)<\/\1>/si',
            function ($matches) use (&$toc, &$slugCount) {
                [$full, $tag, $attrs, $inner] = $matches;

                if (preg_match('/\bid=["\']([^"\']+)["\']/', $attrs, $m)) {
                    $id = $m[1];
                } else {
                    $text = strip_tags($inner);
                    $id = self::slugify($text);

                    if (isset($slugCount[$id])) {
                        $slugCount[$id]++;
                        $id .= '-'.$slugCount[$id];
                    } else {
                        $slugCount[$id] = 0;
                    }

                    $attrs .= ' id="'.$id.'"';
                }

                $toc[] = [
                    'id' => $id,
                    'text' => strip_tags($inner),
                    'level' => (int) substr($tag, 1),
                ];

                return "<{$tag}{$attrs}>{$inner}</{$tag}>";
            },
            $html
        );

        // 归一化层级：找最小 level，所有条目 level = level - minLevel + 1
        if (! empty($toc)) {
            $minLevel = min(array_column($toc, 'level'));
            foreach ($toc as &$item) {
                $item['level'] = $item['level'] - $minLevel + 1;
            }
            unset($item);
        }

        return ['content' => $content, 'toc' => $toc];
    }

    private static function slugify(string $text): string
    {
        // 保留中文、字母、数字，其余转连字符
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', trim($text));

        return strtolower(trim($slug, '-')) ?: 'section';
    }
}
