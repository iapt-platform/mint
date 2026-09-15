<?php

namespace App\DTO\Search;

/**
 * 通用聚合结果DTO
 * 用于处理所有结构相同的聚合桶数据
 */
class AggregationDTO
{
    public function __construct(
        public int $doc_count_error_upper_bound,
        public int $sum_other_doc_count,
        /** @var BucketDTO[] */
        public array $buckets,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            doc_count_error_upper_bound: $data['doc_count_error_upper_bound'],
            sum_other_doc_count: $data['sum_other_doc_count'],
            buckets: array_map(fn ($bucket) => BucketDTO::fromArray($bucket), $data['buckets']),
        );
    }

    public function toArray(): array
    {
        return [
            'doc_count_error_upper_bound' => $this->doc_count_error_upper_bound,
            'sum_other_doc_count' => $this->sum_other_doc_count,
            'buckets' => array_map(
                fn (BucketDTO $bucket) => $bucket->toArray(),
                $this->buckets
            ),
        ];
    }
}
