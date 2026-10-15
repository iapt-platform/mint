<?php

namespace App\Http\Resources\V3;

use App\Services\V3\DiscussionService;
use Illuminate\Http\Request;

/**
 * 某个对象上的话题概况。载荷是 {@see DiscussionService::summary()} 返回的数组。
 */
class DiscussionSummaryResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     status_counts: array{active: int, close: int},
     *     creatable_types: list<string>
     * }
     */
    public function toArray($request): array
    {
        return [
            'status_counts' => $this->resource['status_counts'],
            'creatable_types' => $this->resource['creatable_types'],
        ];
    }
}
