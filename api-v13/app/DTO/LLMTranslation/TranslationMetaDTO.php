<?php

namespace App\DTO\LLMTranslation;

use App\DTO\BaseDTO;

readonly class TranslationMetaDTO extends BaseDTO
{
    public function __construct(
        public int $duration,
        public int $itemsCount,
        public TranslationUsageDTO $usage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            duration: $data['duration'],
            itemsCount: $data['items_count'],
            usage: TranslationUsageDTO::fromArray($data['usage']),
        );
    }
}
