<?php

namespace App\Http\Resources\V3;

use App\Models\Reaction;
use Illuminate\Http\Request;

/**
 * 一条 reaction 操作后的状态：type + 全局计数 + 是否选中 + 记录 id。
 *
 * 组装成品是 Resource 的职责：控制器只把 Reaction 模型传进来（store 后是
 * 已保存的模型，destroy 后是已删除的模型），count / selected / id 都在这里算。
 * selected 直接取模型的 exists 状态——store 后为 true，delete 后为 false。
 */
class ReactionStatusResource extends BaseResource
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
            'type' => $this->type,
            'count' => Reaction::where('target_id', $this->target_id)
                ->where('type', $this->type)
                ->count(),
            'selected' => $this->exists,
            'id' => $this->exists ? $this->id : null,
        ];
    }
}
