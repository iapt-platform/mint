<?php

namespace App\DTO\Search;

class HitsDTO
{
    /**
     * @param  HitItemDTO[]  $items
     */
    public function __construct(
        public int $total,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total']['value'],
            items: array_map(
                fn ($item) => HitItemDTO::fromArray($item),
                $data['hits']
            )
        );
    }
}
