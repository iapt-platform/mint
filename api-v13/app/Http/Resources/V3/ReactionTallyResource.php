<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 某个 type 的计数与「当前用户是否已操作」的摘要（tally 专用）。
 *
 * 输入是控制器已拼好的聚合行 {type, count, selected, id}，这里只负责把类型转对。
 * 与 {@see ReactionStatusResource} 同形状，但那边吃 Reaction 模型、这边吃聚合数组。
 */
class ReactionTallyResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     type: string,
     *     count: int,
     *     selected: bool,
     *     id: string|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'type' => $this->resource['type'],
            'count' => (int) $this->resource['count'],
            'selected' => (bool) $this->resource['selected'],
            'id' => $this->resource['id'] ?? null,
        ];
    }
}
