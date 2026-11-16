<?php

namespace App\Http\Resources\V3;

use App\Models\Share;
use Illuminate\Http\Request;

/**
 * 团队文档里的一项：共享给团队的一个资源。
 *
 * `role` 由 v2 的 power 换来：10 → reader（只读），20 → editor（可编辑）。
 * `resource` 由 Service 按类型批量取好挂上来，**本类不查库**；资源已被删掉时 title / owner_id 为 null。
 *
 * @mixin Share
 */
class GroupShareResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     role: string,
     *     resource: array{type: string, id: string, title: string|null, owner_id: string|null, owner: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null},
     *     created_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'role' => (int) $this->power >= 20 ? 'editor' : 'reader',
            'resource' => $this->getAttribute('resource'),
            'created_at' => $this->created_at,
        ];
    }
}
