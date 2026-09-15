<?php

namespace App\Services;

class TermIndexService
{
    public function __construct(
        protected OpenSearchService $openSearchService,
        protected TermService $termService,
    ) {}

    /**
     * 构建单条词条文档并写入 OpenSearch
     *
     * @param  string  $guid  DhammaTerm 的 guid
     */
    public function index(string $guid): void
    {
        $document = $this->buildDocument($guid);

        $this->openSearchService->create($document['id'], $document);
    }

    /**
     * 构建单条词条的 OpenSearch 文档（不写入）
     *
     * 文档结构遵循新版 mapping：
     *   title.text.pali / title.text.zh  → 全文检索
     *   title.suggest.pali / title.suggest.zh → 自动建议
     *   content.text.pali / content.text.zh   → 正文内容
     *
     * @param  string  $guid  DhammaTerm 的 guid
     * @return array<string, mixed>
     */
    public function buildDocument(string $guid): array
    {
        $termData = $this->termService->find($guid, 'text');
        $channelName = $termData['channel']['name'] ?? '';
        $content = $termData['html'] ?? $termData['meaning'];

        $categories = $this->extractCategories($termData['note'] ?? '');
        $quality = $this->extractFirstQuality($termData['note'] ?? '');
        $tags = [];
        foreach ($categories as $category) {
            $tags[] = "category:{$category}";
        }
        if (! empty($quality)) {
            $tags[] = "quality:{$quality}";
        }

        $document = [
            'id' => "term_{$guid}",
            'resource_id' => $guid,
            'resource_type' => 'term',
            'title' => [
                'text' => [
                    'pali' => $termData['word'],
                    'zh' => $termData['meaning'],
                ],
                'suggest' => [
                    'pali' => [$termData['word']],
                    'zh' => [$termData['meaning']],
                ],
            ],
            'summary' => [
                'text' => $termData['summary'] ?? '',
            ],
            'content' => [],
            'bold_single' => [$termData['meaning'], $termData['word']],
            'related_id' => $termData['word'],
            'category' => null,
            'tags' => $tags,
            'language' => $termData['language'],
            'updated_at' => now()->toIso8601String(),
            'path' => $termData['studio']['realName']."/{$channelName}",
            'metadata' => ['channel' => $termData['channel_id']],
        ];

        // TODO: 补充语言判断，将内容放入对应的 text.pali 或 text.zh 字段
        $plainText = strip_tags($content);
        $document['content']['text']['zh'] = $plainText;
        $document['content']['display'] = $content;             // 展示

        return $document;
    }

    /**
     * 提取 Markdown 中的 {{category|...}} 分类标签
     *
     * @return array<int, string>
     */
    private function extractCategories(string $content): array
    {
        if (empty($content)) {
            return [];
        }
        preg_match_all('/\{\{category\|([^}]+)\}\}/u', $content, $matches);

        return array_values(array_filter(array_map(
            fn ($item) => trim($item),
            $matches[1] ?? []
        )));
    }

    /**
     * 提取 Markdown 中第一个 {{quality|...}} 标签内的内容
     */
    private function extractFirstQuality(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        preg_match('/\{\{quality\|([^}]+)\}\}/u', $content, $matches);

        return isset($matches[1]) ? trim($matches[1]) : '';
    }
}
