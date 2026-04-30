<?php

namespace App\DTO\Search;

class AggregationsDTO
{
    public function __construct(
        public AggregationDTO $granularity,
        public AggregationDTO $resource_type,
        public AggregationDTO $language,
        public AggregationDTO $category,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            granularity: AggregationDTO::fromArray($data['granularity']),
            resource_type: AggregationDTO::fromArray($data['resource_type']),
            language: AggregationDTO::fromArray($data['language']),
            category: AggregationDTO::fromArray($data['category']),
        );
    }

    public function toArray(): array
    {
        return [
            'granularity'   => $this->granularity->toArray(),
            'resource_type' => $this->resource_type->toArray(),
            'language'      => $this->language->toArray(),
            'category'      => $this->category->toArray(),
        ];
    }
}
