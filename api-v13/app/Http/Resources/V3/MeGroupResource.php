<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\GroupMember;
use Illuminate\Http\Request;

/**
 * 我加入的一个团队，带我在里面的角色。载荷是成员记录，团队信息来自预加载的 group。
 *
 * `id` 是团队的 uid：调用方拿它当团队空间的身份、调 `/v3/groups/{group}/members`。
 * 我自己那条成员记录的 id 在 `member_id`，退出团队时用。
 *
 * @mixin GroupMember
 */
class MeGroupResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     name: string,
     *     description: string|null,
     *     owner_id: string,
     *     owner: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     status: int,
     *     role: string,
     *     member_id: string,
     *     created_at: string,
     *     joined_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->group_id,
            'name' => $this->group->name,
            'description' => $this->group->description,
            'owner_id' => $this->group->owner,
            'owner' => $this->actor($this->group->creator),
            'status' => (int) $this->group->status,
            'role' => $this->effectiveRole(),
            'member_id' => (string) $this->id,
            'created_at' => $this->group->created_at,
            'joined_at' => $this->created_at,
        ];
    }
}
