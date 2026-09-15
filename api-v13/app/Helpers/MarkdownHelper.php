<?php

namespace App\Helpers;

class MarkdownHelper
{
    /**
     * 将 Markdown 字符串按段落、标题、表格、列表、代码块等规则拆分成数组
     */
    public static function splitByParagraphs(string $markdown): array
    {
        // 保护代码块内容，防止内部换行被拆分
        $codeBlocks = [];
        $markdown = preg_replace_callback('/```(.*?)```/s', function ($matches) use (&$codeBlocks) {
            $placeholder = '%%CODE_BLOCK_'.count($codeBlocks).'%%';
            $codeBlocks[$placeholder] = $matches[0];

            return $placeholder;
        }, $markdown);

        // 按两个及以上换行符拆分
        $parts = preg_split('/\n\s*\n/', $markdown);

        $result = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            // 恢复代码块
            foreach ($codeBlocks as $placeholder => $codeBlock) {
                if (strpos($part, $placeholder) !== false) {
                    $part = str_replace($placeholder, $codeBlock, $part);
                }
            }

            // 进一步按标题、表格、列表拆分（这些通常不会被两个换行分隔）
            $subParts = self::splitBySpecialBlocks($part);
            foreach ($subParts as $subPart) {
                $subPart = trim($subPart);
                if ($subPart !== '') {
                    $result[] = $subPart;
                }
            }
        }

        return $result;
    }

    /**
     * 按标题、表格、列表等特殊块进一步拆分
     */
    protected static function splitBySpecialBlocks(string $text): array
    {
        // 保护代码块（防止被误拆）
        $codeBlocks = [];
        $text = preg_replace_callback('/```(.*?)```/s', function ($matches) use (&$codeBlocks) {
            $placeholder = '%%CODE_BLOCK_'.count($codeBlocks).'%%';
            $codeBlocks[$placeholder] = $matches[0];

            return $placeholder;
        }, $text);

        // 按标题 (#, ##, 等)
        $lines = preg_split('/(^#{1,6}\s+.*$)/m', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // 如果包含表格（简单判断：包含 | 且多个行）
            if (strpos($line, '|') !== false && substr_count($line, "\n") >= 1) {
                $rows = explode("\n", $line);
                $table = [];
                foreach ($rows as $row) {
                    if (trim($row) !== '') {
                        $table[] = $row;
                    }
                }
                if (! empty($table)) {
                    $result[] = implode("\n", $table);
                }

                continue;
            }

            // 如果包含列表（无序列表 - 或 *，有序列表 1. 2.）
            if (preg_match('/^(\s*[-*+]\s+|\s*\d+\.\s+)/m', $line)) {
                // 保留整个列表块（连续列表行）
                $result[] = $line;

                continue;
            }

            // 恢复代码块
            foreach ($codeBlocks as $placeholder => $codeBlock) {
                if (strpos($line, $placeholder) !== false) {
                    $line = str_replace($placeholder, $codeBlock, $line);
                }
            }

            $result[] = $line;
        }

        return $result;
    }
}
