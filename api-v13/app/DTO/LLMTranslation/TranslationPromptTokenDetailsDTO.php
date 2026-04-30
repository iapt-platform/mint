<?php

namespace App\DTO\LLMTranslation;

use App\DTO\BaseDTO;

readonly class TranslationPromptTokenDetailsDTO  extends BaseDTO
{
    public function __construct(
        public int $cachedTokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cachedTokens: $data['cached_tokens'],
        );
    }
}
