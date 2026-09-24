<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\Reaction;
use Illuminate\Http\Request;

/**
 * 一条 reaction 记录。
 *
 * user 是操作者的公开摘要。操作者可能是人类用户（`user_infos`）也可能是 AI 助手
 * （`ai_models`），两张表的形状不同，这里只承诺稳定的核心字段。
 *
 * **本类不查库。** 操作者从 `user` / `aiModel` 两条已预加载的关系上取——
 * 控制器负责 `->with([...])`，见 {@see Reaction::user()}。
 * {@see ResolvesActor::actor()} 只把已加载的模型拼成摘要，不碰数据库。
 */
class ReactionResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     type: string,
     *     target_id: string,
     *     target_type: string,
     *     context: string|null,
     *     created_at: string,
     *     updated_at: string,
     *     user: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'target_id' => $this->target_id,
            'target_type' => $this->target_type,
            'context' => $this->context,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->actor($this->user, $this->aiModel),
        ];
    }
}
