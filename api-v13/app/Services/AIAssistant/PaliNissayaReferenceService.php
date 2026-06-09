<?php

namespace App\Services\AIAssistant;

use App\Models\Sentence;

/**
 * 提取指定段落的 nissaya（巴利原文逐词缅文释义），作为翻译 / 审校 / 评估的参考资料。
 *
 * nissaya 是缅甸传统的逐词释义：每个巴利词后给出其语法解析与缅文释义
 * （格式形如「巴利词= 缅文释义。」），是判断词义、修饰关系、指代关系与
 * 句子结构的权威依据。该模块只负责按段落取数，可复用于任意工作流步骤。
 */
class PaliNissayaReferenceService
{
    /**
     * 按句子提取段落的 nissaya 原文，返回 ['id' => ..., 'content' => ...]。
     * id 与巴利原文 / 译文一致（book-para-word_start-word_end），便于按句对应。
     * 无 nissaya 数据时返回空数组。
     *
     * @return array<int, array{id: string, content: string}>
     */
    public function forParagraph(int $book, int $para): array
    {
        $sentences = Sentence::nissaya()
            ->language('my') // 缅文 nissaya
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->orderBy('word_start')
            ->get();

        $result = [];
        foreach ($sentences as $sentence) {
            if (empty($sentence->content)) {
                continue;
            }
            $id = "{$sentence->book_id}-{$sentence->paragraph}-{$sentence->word_start}-{$sentence->word_end}";
            $result[] = ['id' => $id, 'content' => $sentence->content];
        }

        return $result;
    }
}
