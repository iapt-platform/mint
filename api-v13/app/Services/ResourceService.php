<?php

namespace App\Services;

use League\CommonMark\GithubFlavoredMarkdownConverter;

class ResourceService
{
    protected $openSearch;

    protected $embeddingService;

    protected $markdownConverter;

    public function __construct(OpenSearchService $openSearch, EmbeddingService $embeddingService)
    {
        $this->openSearch = $openSearch;
        $this->embeddingService = $embeddingService;
        $this->markdownConverter = new GithubFlavoredMarkdownConverter;
    }

    public function store(array $data)
    {
        $doc = $this->buildDocument($data);

        return $this->openSearch->create('wikipali_resources', $doc['id'], $doc);
    }

    public function update($uid, array $data)
    {
        $doc = $this->buildDocument(array_merge(['uid' => $uid], $data));

        return $this->openSearch->create('wikipali_resources', $doc['id'], $doc); // 使用 create 覆盖更新
    }

    public function delete($uid)
    {
        $this->openSearch->delete('wikipali_resources', $uid);
    }

    public function generateEmbedding($text)
    {
        return $this->embeddingService->generate($text);
    }

    private function buildDocument(array $data)
    {
        $contentText = is_array($data['content']) ? $data['content'][0]['text'] ?? '' : $data['content'];
        $normalizedText = $this->normalizeMarkdown($contentText);

        $doc = [
            'id' => $data['uid'],
            'uid' => $data['uid'],
            'type' => $data['type'],
            'title' => $data['title'],
            'content' => [
                [
                    'id' => $data['uid'],
                    'text' => $contentText,
                    'text_normalized' => $normalizedText,
                ],
            ],
            'confidence' => $data['confidence'] ?? 1.0,
            'content_embedding' => $this->embeddingService->generate($normalizedText),
            'suggest_content' => $this->extractSuggestions($contentText, $data['title']),
            'metadata' => $data['metadata'] ?? [],
        ];

        if (in_array($data['type'], ['sutta', 'paragraph'])) {
            $doc['book_id'] = $data['book_id'] ?? null;
            $doc['paragraph'] = $data['paragraph'] ?? null;
            $doc['scripture_id'] = $data['book_id'] && $data['paragraph'] ? "{$data['book_id']}-{$data['paragraph']}" : null;
            $doc['path_full'] = $data['path_full'] ?? '';
            $doc['path_embedding'] = $this->embeddingService->generate($doc['path_full']);
            $doc['book_name'] = $data['book_name'] ?? '';
            $doc['vagga'] = $data['vagga'] ?? '';
            $doc['chapter'] = $data['chapter'] ?? '';
            $doc['sutta_name'] = $data['sutta_name'] ?? $data['title'];
        }

        return $doc;
    }

    private function normalizeMarkdown($markdown)
    {
        // 转换为纯文本，去除 Markdown 标记
        $html = $this->markdownConverter->convert($markdown)->getContent();
        $text = strip_tags($html);
        // 简单巴利文规范化（可进一步用 ICU folding）
        $text = str_replace(['ā', 'ī', 'ū'], ['a', 'i', 'u'], strtolower($text));

        return $text;
    }

    private function extractSuggestions($markdown, $title)
    {
        $text = $this->normalizeMarkdown($markdown);
        // 提取标题和关键词（简单示例，可用 NLP 优化）
        $keywords = array_unique(array_filter(explode(' ', $text), fn ($word) => strlen($word) > 2));

        return array_merge([$title], array_slice($keywords, 0, 5));
    }
}
