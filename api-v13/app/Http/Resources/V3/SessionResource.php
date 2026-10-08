<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一次登录的结果：bearer token + 当前用户资料。
 *
 * token 是 JWT，有效期一年，与 v2 `POST /v2/sign-in` 签出的完全相同、两边互认。
 * 服务端不保存会话，退出登录就是客户端丢掉 token。
 */
class SessionResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     token: string,
     *     user: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: string[], email: string}
     * }
     */
    public function toArray($request): array
    {
        return [
            'token' => $this->resource['token'],
            'user' => MeResource::make($this->resource['user'])->toArray($request),
        ];
    }
}
