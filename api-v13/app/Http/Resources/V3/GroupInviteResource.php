<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Services\V3\GroupInviteService;
use Illuminate\Http\Request;

/**
 * 打开邀请链接看到的内容：哪个团队、谁邀请的、以什么角色加入。
 *
 * `member_role` 是查看者在这个团队里现有的角色：没登录或还不是成员为 null。
 * 载荷由 {@see GroupInviteService::preview()} 组好，团队的 creator 已预加载，本类不查库。
 */
class GroupInviteResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     group: array{id: string, name: string, description: string|null, status: int, owner: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null},
     *     inviter: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null},
     *     role: string,
     *     expires_at: string,
     *     member_role: string|null
     * }
     */
    public function toArray($request): array
    {
        $group = $this->resource['group'];

        return [
            'group' => [
                'id' => $group->uid,
                'name' => $group->name,
                'description' => $group->description,
                'status' => (int) $group->status,
                'owner' => $this->actor($group->creator),
            ],
            'inviter' => $this->resource['inviter'],
            'role' => $this->resource['role'],
            'expires_at' => $this->resource['expires_at'],
            'member_role' => $this->resource['member']?->effectiveRole(),
        ];
    }
}
