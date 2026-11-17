<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\GroupInfo;
use App\Models\TermCategory;
use App\Models\UserInfo;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 术语写进 OpenSearch（全站搜索用）。v2 与 v3 的写入、`IndexTerm` 命令共用。
 *
 * 渲染用新渲染器（{@see MarkupService}）：内容 = 摘要 + 正文，按 content_type 的方言解析，
 * 模板按术语所在 channel 取数。分类与质量优先取 v3 的列（term_categories、quality），
 * 没有时（v2 写入的条目）从正文的 `{{category|…}}` / `{{quality|…}}` 抽。
 */
class TermIndexService
{
    public function __construct(
        protected OpenSearchService $openSearchService,
        protected MarkupService $markup,
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
     *
     * @throws ModelNotFoundException 术语不存在（含已删除）
     */
    public function buildDocument(string $guid): array
    {
        $term = DhammaTerm::where('guid', $guid)->firstOrFail();
        $channelName = $term->channal ? (string) Channel::where('uid', $term->channal)->value('name') : '';
        $studioName = (string) (UserInfo::where('userid', $term->owner)->value('username')
            ?? GroupInfo::where('uid', $term->owner)->value('name')
            ?? '');

        $summary = trim((string) $term->summary);
        $options = [
            'channels' => $term->channal ? [$term->channal] : [],
            'contentType' => $term->effectiveContentType(),
        ];
        $rendered = $this->markup->renderManyAs([
            'body' => $options + ['source' => trim($summary."\n\n".(string) $term->note)],
            'summary' => $options + ['source' => $summary !== '' ? $summary : (string) $term->note],
        ], new RenderOptions(lang: $term->language ?: 'zh-Hans'), [Format::Html, Format::Text]);
        $display = (string) $rendered[Format::Html->value]['body'];
        $plainText = (string) $rendered[Format::Text->value]['body'];
        $summaryText = $this->firstLine((string) $rendered[Format::Text->value]['summary'], $summary !== '');

        $categories = TermCategory::where('term_guid', $term->guid)->orderBy('position')->pluck('category')->all()
            ?: $this->extractCategories((string) $term->note);
        $quality = $term->quality ?: $this->extractFirstQuality((string) $term->note);
        $tags = [];
        foreach ($categories as $category) {
            $tags[] = "category:{$category}";
        }
        if (! empty($quality)) {
            $tags[] = "quality:{$quality}";
        }

        return [
            'id' => "term_{$term->guid}",
            'resource_id' => $term->guid,
            'resource_type' => 'term',
            'title' => [
                'text' => [
                    'pali' => $term->word,
                    'zh' => $term->meaning,
                ],
                'suggest' => [
                    'pali' => [$term->word],
                    'zh' => [$term->meaning],
                ],
            ],
            'summary' => [
                'text' => $summaryText,
            ],
            // TODO: 补充语言判断，将内容放入对应的 text.pali 或 text.zh 字段
            'content' => [
                'text' => ['zh' => $plainText !== '' ? $plainText : (string) $term->meaning],
                'display' => $display !== '' ? $display : (string) $term->meaning,
            ],
            'bold_single' => [$term->meaning, $term->word],
            'related_id' => $term->word,
            'category' => null,
            'tags' => $tags,
            'language' => $term->language,
            'updated_at' => now()->toIso8601String(),
            'path' => "{$studioName}/{$channelName}",
            'metadata' => ['channel' => $term->channal],
        ];
    }

    /**
     * 摘要纯文本：给了 summary 就整段用；没给取正文第一段（纯文本里段落之间只隔一个换行）。
     */
    private function firstLine(string $text, bool $whole): string
    {
        if ($whole) {
            return trim($text);
        }
        foreach (preg_split('/\n/u', $text) ?: [] as $line) {
            if (trim($line) !== '') {
                return trim($line);
            }
        }

        return '';
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
