<?php

namespace App\DTO\Search;

class QueryInfoDTO
{
    public function __construct(
        public string $original_query,
        public string $search_mode,
        public string $request_method,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            original_query: $data['original_query'],
            search_mode: $data['search_mode'],
            request_method: $data['request_method'],
        );
    }
}
