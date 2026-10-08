<?php

namespace App\Http\Resources\V3;

use App\Services\UserService;
use App\Services\V3\SessionService;
use Illuminate\Http\Request;

/**
 * 当前登录用户本人的资料。
 *
 * 前几个字段与 v3 其他资源里的 user 摘要同形（{@see UserService::profile()}，
 * 驼峰是 v2 留下的既有口径，前端的 IUser 就是这个形状）；roles 恒为数组，
 * 另加只有本人能看的 email。
 *
 * 载荷是 {@see SessionService} 拼好的数组。
 */
class MeResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     nickName: string,
     *     userName: string,
     *     realName: string,
     *     sn: int,
     *     avatar: string|null,
     *     roles: string[],
     *     email: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource['id'],
            'nickName' => $this->resource['nickName'],
            'userName' => $this->resource['userName'],
            'realName' => $this->resource['realName'],
            'sn' => $this->resource['sn'],
            'avatar' => $this->resource['avatar'],
            'roles' => $this->resource['roles'],
            'email' => $this->resource['email'],
        ];
    }
}
