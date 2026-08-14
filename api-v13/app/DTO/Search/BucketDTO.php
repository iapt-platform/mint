<?php

namespace App\DTO\Search;

class BucketDTO
{
    public function __construct(
        public string $key,
        public int $doc_count,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            doc_count: $data['doc_count'],
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'doc_count' => $this->doc_count,
        ];
    }
}
