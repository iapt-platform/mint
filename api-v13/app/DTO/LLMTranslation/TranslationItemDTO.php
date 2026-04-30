<?php

namespace App\DTO\LLMTranslation;

use App\DTO\BaseDTO;

readonly class TranslationItemDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            content: $data['content'],
        );
    }
}
