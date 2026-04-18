<?php

namespace App\DTO\Search;

class SearchDataDTO
{
    public function __construct(
        public int $took,
        public bool $timed_out,
        public ShardsDTO $shards,
        public HitsDTO $hits,
        public AggregationsDTO $aggregations,

    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            took: $data['took'],
            timed_out: $data['timed_out'],
            shards: ShardsDTO::fromArray($data['_shards']),
            hits: HitsDTO::fromArray($data['hits']),
            aggregations: AggregationsDTO::fromArray($data['aggregations']),
        );
    }
}
