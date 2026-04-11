<?php

namespace App\DTO\Search;

class SearchDataDTO
{
    public function __construct(
        public int $took,
        public HitsDTO $hits,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            took: $data['took'],
            hits: HitsDTO::fromArray($data['hits'])
        );
    }
}
