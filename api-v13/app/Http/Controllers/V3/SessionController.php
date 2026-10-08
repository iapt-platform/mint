<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreSessionRequest;
use App\Http\Resources\V3\SessionResource;
use App\Services\V3\SessionService;

/**
 * 登录。
 *
 * 取代 `POST /v2/sign-in`（`AuthController@signIn`）+ 紧随其后的
 * `GET /v2/auth/current`。v2 那两条在 dashboard-v4 下线前原样保留，不要改动；
 * wikipali-mobile 目前也还在用它们。
 */
class SessionController extends Controller
{
    public function __construct(private readonly SessionService $sessions) {}

    /**
     * 登录
     *
     * 用用户名或邮箱 + 密码换取 bearer token，同时返回当前用户资料，前端不必再查一次。
     * 账号或密码不对返回 422（errors.login），不区分是哪一个错。
     * 按 IP 与账号限流，超出返回 429。
     *
     * @unauthenticated
     *
     * @bodyParam login string required 用户名或邮箱（邮箱不区分大小写）
     * @bodyParam password string required 密码
     *
     * @responseStatus 201 登录成功
     * @responseStatus 429 尝试过于频繁
     */
    public function store(StoreSessionRequest $request)
    {
        return SessionResource::make(
            $this->sessions->signIn($request->validated('login'), $request->validated('password'))
        )->response()->setStatusCode(201);
    }
}
