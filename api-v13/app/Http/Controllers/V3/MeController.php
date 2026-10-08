<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\V3\MeResource;
use App\Services\V3\SessionService;
use Illuminate\Http\Request;

/**
 * 当前登录用户本人。
 *
 * 取代 `GET /v2/auth/current`（`AuthController@getUserInfoByToken`）。v2 那份在
 * dashboard-v4 下线前原样保留，不要改动；wikipali-mobile 目前也还在用它。
 */
class MeController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly SessionService $sessions) {}

    /**
     * 我的资料
     *
     * 前端启动时用已存的 token 调它恢复登录态；401 表示 token 无效或过期，应清掉 token。
     * 不回传 token（v2 的 auth/current 会把请求里的 token 原样回显）。
     */
    public function show(Request $request): MeResource
    {
        return MeResource::make($this->sessions->me($this->currentUser($request)['user_uid']));
    }
}
