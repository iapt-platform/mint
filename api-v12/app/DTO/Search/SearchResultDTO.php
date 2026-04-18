<?php

namespace App\DTO\Search;

class SearchResultDTO
{
    public function __construct(
        public bool $success,
        public SearchDataDTO $data,
        public QueryInfoDTO $query_info,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            data: SearchDataDTO::fromArray($data['data']),
            query_info: QueryInfoDTO::fromArray($data['query_info']),
        );
    }
}
