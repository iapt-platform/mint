<?php

namespace App\DTO\Search;

class SearchResultDTO
{
    public function __construct(
        public bool $success,
        public SearchDataDTO $data,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            data: SearchDataDTO::fromArray($data['data'])
        );
    }
}
