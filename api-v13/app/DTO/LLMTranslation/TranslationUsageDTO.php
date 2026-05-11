<?php

namespace App\DTO\LLMTranslation;

use App\DTO\BaseDTO;

readonly class TranslationUsageDTO extends BaseDTO
{
    public function __construct(
        public int $promptTokens,
        public int $completionTokens,
        public int $totalTokens,
        public ?TranslationPromptTokenDetailsDTO $promptTokensDetails = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            promptTokens: $data['prompt_tokens'],
            completionTokens: $data['completion_tokens'],
            totalTokens: $data['total_tokens'],
            promptTokensDetails: isset($data['prompt_tokens_details']) ? TranslationPromptTokenDetailsDTO::fromArray(
                $data['prompt_tokens_details']
            ) : null,
        );
    }
}
