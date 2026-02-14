<?php

namespace App\Services;

class NissayaParser
{
    /**
     * 解析nissaya巴利文-缅文文本
     *
     * @param string $content
     * @return array
     */
    public function parse(string $content): array
    {
        $lines = explode("\n", $content);
        $records = [];
        $currentRecord = null;
        $pendingNotes = [];
        $inCodeBlock = false;
        $codeBlockContent = '';
        $codeBlockDelimiter = '';

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmedLine = trim($line);

            // 检测代码块开始/结束 (支持 ``` 和 ``)
            if (preg_match('/^(```|``)$/', $trimmedLine, $matches)) {
                if (!$inCodeBlock) {
                    // 开始代码块
                    $inCodeBlock = true;
                    $codeBlockDelimiter = $matches[1];
                    $codeBlockContent = '';
                } elseif ($matches[1] === $codeBlockDelimiter) {
                    // 结束代码块
                    $inCodeBlock = false;
                    $pendingNotes[] = trim($codeBlockContent);
                    $codeBlockContent = '';
                    $codeBlockDelimiter = '';
                }
                continue;
            }

            // 在代码块内
            if ($inCodeBlock) {
                $codeBlockContent .= $line . "\n";
                continue;
            }

            // 空行跳过
            if (empty($trimmedLine)) {
                continue;
            }

            // 检查是否包含等号
            if (strpos($line, '=') !== false) {
                // 检查是否是以等号开头(补充上一条记录的翻译)
                if (strpos(ltrim($line), '=') === 0) {
                    // 这是对上一条记录的翻译补充
                    if ($currentRecord !== null && empty($currentRecord['translation'])) {
                        $currentRecord['translation'] = trim(substr(ltrim($line), 1));
                    }
                } else {
                    // 保存之前的记录
                    if ($currentRecord !== null) {
                        $currentRecord['notes'] = $pendingNotes;
                        $records[] = $currentRecord;
                        $pendingNotes = [];
                    }

                    // 解析新记录
                    list($original, $translation) = explode('=', $line, 2);
                    $currentRecord = [
                        'original' => trim($original),
                        'translation' => trim($translation),
                        'notes' => []
                    ];
                }
            } else {
                // 没有等号的行
                if ($currentRecord !== null && empty($currentRecord['translation'])) {
                    // 情况1: 上一行只有巴利文(等号后为空),当前行是缅文翻译
                    $currentRecord['translation'] = trim($line);
                } elseif ($currentRecord === null) {
                    // 情况2: 第一行没有等号,可能是不完整的巴利文
                    $currentRecord = [
                        'original' => trim($line),
                        'translation' => '',
                        'notes' => []
                    ];
                } else {
                    // 其他情况视为注释内容
                    $pendingNotes[] = trim($line);
                }
            }
        }

        // 保存最后一条记录
        if ($currentRecord !== null) {
            $currentRecord['notes'] = $pendingNotes;
            $records[] = $currentRecord;
        }

        return $records;
    }

    /**
     * 解析文件
     *
     * @param string $filePath
     * @return array
     */
    public function parseFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("文件不存在: {$filePath}");
        }

        $content = file_get_contents($filePath);
        return $this->parse($content);
    }
}
