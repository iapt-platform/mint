<?php

namespace App\Http\Resources\V3;

use App\Models\Invite;
use Illuminate\Http\Request;

/**
 * 一条注册邀请（`invites` 表）。
 *
 * id 就是注册凭据：只有邮箱主人能拿到（邀请邮件里，或比对验证码之后）。
 * 不返回邀请人 user_uid——注册页用不到。
 *
 * @mixin Invite
 */
class InviteResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     email: string,
     *     status: string,
     *     created_at: string|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
