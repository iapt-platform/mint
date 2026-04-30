<?php

/**
 * 使用方式
 * use App\DTO\LLMTranslation\TranslationResponseDTO;

$dto = TranslationResponseDTO::fromArray($response);

dd($dto->data[0]->content);
 */

namespace App\DTO\LLMTranslation;

use App\DTO\BaseDTO;

readonly class TranslationResponseDTO extends BaseDTO
{
    /**
     * @param TranslationItemDTO[] $data
     */
    public function __construct(
        public bool $success,
        public string $error,
        public array $data,
        public TranslationMetaDTO $meta,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            success: $payload['success'],
            error: '',
            data: array_map(
                fn(array $item) => TranslationItemDTO::fromArray($item),
                $payload['data']
            ),

            meta: TranslationMetaDTO::fromArray($payload['meta']),
        );
    }
}
