<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 某个 type 的计数与「当前用户是否已操作」的摘要（tally 专用）。
 *
 * 输入是控制器已拼好的聚合行 {type, count, selected, id}，这里只负责把类型转对。
 * 计数只在这里出现。写端点（store / destroy）不返回聚合值——那是共享的、会被别人
 * 改的，mutation 的响应发出去就可能过期。见 v3-resource skill 硬规范第 2 条。
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
