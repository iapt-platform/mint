<?php

namespace App\DTO\Search;

class ShardsDTO
{
    public function __construct(
        public int $total,
        public int $successful,
        public int $skipped,
        public int $failed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            successful: $data['successful'],
            skipped: $data['skipped'],
            failed: $data['failed'],
        );
    }
}
