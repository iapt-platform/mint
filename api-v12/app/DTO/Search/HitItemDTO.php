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
    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['_source'];

        return new self(
            id: $source['id'],
            score: $data['_score'],
            title: $source['title']['pali'] ?? '',
            content: $source['content']['pali'] ?? '',
            path: $source['path'] ?? '',
            category: $source['category'] ?? [],
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
