<?php

namespace App\Http\Resources\V3;

use App\Services\V3\PasswordResetService;
use Illuminate\Http\Request;

/**
 * 一次待完成的密码重置：只告诉持 token 的人「你在给哪个账号改密码」、「链接何时失效」。
 *
 * 载荷是 {@see PasswordResetService::find()} 返回的数组，不是模型——
 * 别在这里暴露 user_infos 的其他列（v2 的 reset-password 曾把整行连同密码哈希吐出去）。
 */
class PasswordResetResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     username: string,
     *     expires_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'username' => $this->resource['username'],
            'expires_at' => $this->resource['expires_at']->toIso8601ZuluString(),
        ];
    }
}
