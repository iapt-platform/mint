<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreInviteRequest;
use App\Http\Resources\V3\InviteResource;
use App\Services\V3\SignUpService;

/**
 * 注册邀请。invite id 是 `POST /v3/users` 的注册凭据。
 *
 * - show 取代 `GET /v2/invite/{invite}`（`InviteController@show`），邀请注册页用。
 * - store 取代 v2「`GET /v2/email-certification/{id}` 取回验证码在前端比对」那一步：
 *   改成服务端比对验证码，通过后签发 invite。
 *
 * studio 发邀请（`POST /v2/invite`，要登录、要 studio 权限）是另一个契约，
 * 将来迁成嵌套资源 `studios/{studio}/invites`，不放在这里。
 * v2 那几条在 dashboard-v4 下线前原样保留，不要改动。
 */
class InviteController extends Controller
{
    public function __construct(private readonly SignUpService $signUp) {}

    /**
     * 用验证码换注册邀请
     *
     * 验证码正确则返回该邮箱的 invite（已有则复用，状态重置为未使用），验证码随即作废。
     * 验证码错误、过期或试错 5 次后返回 422（errors.code）。
     *
     * @unauthenticated
     *
     * @bodyParam email string required 收到验证码的邮箱
     * @bodyParam code string required 邮件里的 6 位验证码。Example: 123456
     *
     * @responseStatus 201 首次为该邮箱签发 invite。响应体与 200 同形；复用已有 invite 时是 200
     * @responseStatus 429 请求过于频繁
     */
    public function store(StoreInviteRequest $request): InviteResource
    {
        return InviteResource::make(
            $this->signUp->verifyCode($request->validated('email'), $request->validated('code'))
        );
    }

    /**
     * 查看一条可用的注册邀请
     *
     * 邀请注册页用它显示邮箱。不存在或已被使用都返回 404。
     *
     * @unauthenticated
     *
     * @urlParam invite string required 邀请邮件链接里的 uuid
     *
     * @responseStatus 404 邀请不存在或已被使用
     */
    public function show(string $invite): InviteResource
    {
        return InviteResource::make($this->signUp->findPendingInvite($invite));
    }
}
