<?php

namespace App\DTO\Search;

class HitItemDTO
{
    public function __construct(
        public string $id,
        public string $resId,
        public float $score,
        public string $content,
        public string $title,
        public string $path,
        public array $category,
        public array $tags,
        public string $highlight,
        public string $updated,
        public string $type,
        public string $language,
        public ?string $display = null,
    ) {}

    /**
     * 从 OpenSearch hit 数组构建 DTO
     *
     * title 和 content 的拼接策略：
     *   因为一条记录通常只有一种语言被赋值（pali 或 zh），
     *   所以直接将 text.* 下所有非空值拼接输出，无需判断语言类型。
     *
     *   title:   取 title.text.pali + title.text.zh（跳过空值）
     *   content: 取 content.text.pali + content.text.zh（跳过空值）
     *   display: 取 content.display，列表页查询时因被 sourceExcludes 排除
     *            通常为 null；详情页通过 get() 获取完整文档时才有值
     *
     * @param  array  $data  OpenSearch 单条 hit 原始数据
     * @return static
     */
    public static function fromArray(array $data): self
    {
        $source = $data['_source'];

        // ---------- highlight ----------
        $highlightArray = [];
        if (! empty($data['highlight']) && is_array($data['highlight'])) {
            foreach ($data['highlight'] as $fragments) {
                $highlightArray = array_merge($highlightArray, $fragments);
            }
        }

        // ---------- title ----------
        // 取 title.text 下所有非空语言值拼接
        $titleText = $source['title']['text'] ?? [];
        $title = implode('', array_filter(
            is_array($titleText) ? array_values($titleText) : [],
            fn ($v) => is_string($v) && $v !== ''
        ));

        // ---------- content ----------
        // 取 content.text 下所有非空语言值拼接
        $contentText = $source['content']['text'] ?? [];
        $content = implode('', array_filter(
            is_array($contentText) ? array_values($contentText) : [],
            fn ($v) => is_string($v) && $v !== ''
        ));

        // ---------- display ----------
        // 列表页查询时被 sourceExcludes 排除，值为 null
        // 详情页通过 get() 取完整文档时才有值
        $display = $source['content']['display'] ?? null;

        // ---------- category ----------
        $rawCategory = $source['category'] ?? [];
        $category = is_array($rawCategory) ? $rawCategory : [$rawCategory];

        return new self(
            id: $source['id'],
            score: $data['_score'] ?? 0,
            title: $title,
            content: $content,
            path: $source['path'] ?? '',
            category: $category,
            tags: $source['tags'] ?? [],
            highlight: implode('', $highlightArray),
            updated: $source['updated_at'],
            type: $source['resource_type'],
            language: $source['language'],
            resId: $source['resource_id'],
            display: $display,
        );
    }

    /**
     * 将 DTO 转为数组（用于 API 响应输出）
     *
     * display 字段仅在有值时输出，避免列表页响应体携带 null 键。
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'res_id' => $this->resId,
            'score' => $this->score,
            'title' => $this->title,
            'content' => $this->content,
            'path' => $this->path,
            'category' => $this->category,
            'tags' => $this->tags,
            'highlight' => $this->highlight,
            'updated' => $this->updated,
            'type' => $this->type,
            'language' => $this->language,
            'para_id' => $this->getParaId(),
            'para_link' => $this->getParaLink(),
        ];

        if ($this->display !== null) {
            $data['display'] = $this->display;
        }

        return $data;
    }

    /**
     * 提取 para 引用 ID
     *
     * 匹配文档 ID 格式 "pali_para_{bookId}_{paraId}"，
     * 返回 "{bookId}-{paraId}" 格式字符串。
     */
    public function getParaId(): ?string
    {
        if (preg_match('/tipitaka_paragraph_pi_(\d+)-(\d+)/', $this->id, $matches)) {
            return "{$matches[1]}-{$matches[2]}";
        }

        return null;
    }

    /**
     * 生成 para wiki 模板引用字符串
     *
     * @return string|null 例如：{{para|id=1-23|title=1-23|style=reference}}
     */
    public function getParaLink(): ?string
    {
        $id = $this->getParaId();
        if (! $id) {
            return null;
        }

        return "{{para|id={$id}|title={$id}|style=reference}}";
    }
}
