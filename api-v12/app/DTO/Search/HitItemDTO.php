<?php

namespace App\DTO\Search;

class HitItemDTO
{
    public function __construct(
        public string $id,
        public float $score,
        public string $content,
        public string $title,
        public string $path,
        public array $category,
        public string $highlight,

    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['_source'];
        $highlight = $data['highlight'] ?? null;
        $highlightArray = [];
        if (is_array($highlight)) {
            foreach ($highlight as $key => $value) {
                $highlightArray = array_merge($highlightArray, $value);
            }
        }

        $content = $source['content'];
        $contentArray = [];
        if (is_array($content)) {
            foreach ($content as $key => $value) {
                $contentArray[] = $value;
            }
        }
        $titleArray = [];
        if (is_array($source['title'])) {
            foreach ($source['title'] as $key => $value) {
                $titleArray[] = $value;
            }
        }
        $title = implode('', $titleArray);

        $category = [];
        if (is_array($source['category'])) {
            $category = $source['category'];
        } else {
            $category = [$source['category']];
        }

        return new self(
            id: $source['id'],
            score: $data['_score'],
            title: $title,
            content: implode('', $contentArray),
            path: $source['path'] ?? '',
            category: $category,
            highlight: implode('', $highlightArray),
        );
    }



    /**
     * 提取 para 引用ID（核心逻辑🔥）
     */
    public function getParaId(): ?string
    {
        if (preg_match('/pali_para_(\d+)_(\d+)/', $this->id, $matches)) {
            return "{$matches[1]}-{$matches[2]}";
        }
        return null;
    }

    public function getParaLink(): ?string
    {
        $id = $this->getParaId();
        if (!$id) return null;

        return "{{para|id={$id}|title={$id}|style=reference}}";
    }
}
