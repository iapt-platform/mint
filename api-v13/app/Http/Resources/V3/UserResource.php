<?php

namespace App\Http\Resources\V3;

use App\Models\UserInfo;
use Illuminate\Http\Request;

/**
 * 账号本人能看到的账号信息（`user_infos` 表）。
 *
 * id 是 `userid`（uuid），不是自增主键——与 v2 的用户对象口径一致。
 * 只列白名单字段：密码哈希、各种 token 不出这个类。
 *
 * @mixin UserInfo
 */
class UserResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     username: string,
     *     nickname: string,
     *     email: string,
     *     created_at: string|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->userid,
            'username' => $this->username,
            'nickname' => $this->nickname,
            'email' => $this->email,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
