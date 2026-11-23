<?php

namespace App\Http\Resources\V3;

use App\Services\V3\GroupInviteService;
use Illuminate\Http\Request;

/**
 * 一张团队邀请链接：客户端把 token 拼进自己的邀请页地址发给别人。
 *
 * 载荷由 {@see GroupInviteService::issue()} 组好，本类只做形状声明。
 */
class GroupInviteLinkResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     token: string,
     *     role: string,
     *     expires_at: string
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
