<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\GroupMember;
use Illuminate\Http\Request;

/**
 * 团队里的一名成员。
 *
 * `role` 是实际角色（{@see GroupMember::effectiveRole()}），v2 写入、role 列为空的行
 * 也会按 power 推出来。**本类不查库**：user 来自控制器预加载的关系。
 *
 * @mixin GroupMember
 */
class GroupMemberResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     group_id: string,
     *     role: string,
     *     joined_at: string,
     *     user: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'group_id' => $this->group_id,
            'role' => $this->effectiveRole(),
            'joined_at' => $this->created_at,
            'user' => $this->actor($this->user),
        ];
    }
}
