<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\GroupInfo;
use Illuminate\Http\Request;

/**
 * 一个团队。
 *
 * `role` 是**查看者**在团队里的角色（不是成员就是 null），只在详情、新建、修改时给，
 * 搜索列表里没有。`owner` 是创建者的公开摘要。**本类不查库**：creator 来自预加载，
 * role 由 Service 挂在 `viewer_role` 上。
 *
 * @mixin GroupInfo
 */
class GroupResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     name: string,
     *     description: string|null,
     *     status: int,
     *     owner: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     role?: string|null,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->uid,
            'name' => $this->name,
            'description' => $this->description,
            'status' => (int) $this->status,
            'owner' => $this->actor($this->creator),
            'role' => $this->when(array_key_exists('viewer_role', $this->resource->getAttributes()), fn () => $this->viewer_role),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
