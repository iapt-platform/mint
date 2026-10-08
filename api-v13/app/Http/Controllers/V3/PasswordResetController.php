<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StorePasswordResetRequest;
use App\Http\Requests\V3\UpdatePasswordResetRequest;
use App\Http\Resources\V3\PasswordResetResource;
use App\Services\V3\PasswordResetService;

/**
 * 找回密码。
 *
 * 取代 `POST /v2/auth/forgot-password`、`GET /v2/auth/reset-password/{token}`、
 * `POST /v2/auth/reset-password`（`ForgotPasswordController` / `ResetPasswordController`）。
 * v2 那几条在 dashboard-v4 下线前原样保留，不要改动。
 *
 * 一次「密码重置」是一个以 token 为标识的资源：store 发起（发邮件），
 * show 查看（给哪个账号、何时过期），update 完成（设新密码，token 作废）。
 */
class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $resets) {}

    /**
     * 申请重置密码（发邮件）
     *
     * 无论邮箱是否注册都返回 204，不泄露账号是否存在。邮件是同步发出的，
     * 返回时已经发完，所以是 204 而不是 202。邮件里的链接指向
     * dashboard-v6 的 `/anonymous/reset-password/{token}`，60 分钟内有效。
     *
     * 按 IP 与邮箱限流，超出返回 429。
     *
     * @unauthenticated
     *
     * @bodyParam email string required 账号邮箱。Example: someone@example.com
     * @bodyParam lang string 邮件语言，没有对应模板的回落 en。Enum: en,en-US,zh-Hans,zh-Hant
     *
     * @responseStatus 204 已发送（或邮箱未注册），无响应体
     * @responseStatus 429 请求过于频繁
     * @responseStatus 503 邮件发送失败
     */
    public function store(StorePasswordResetRequest $request)
    {
        $this->resets->request($request->validated('email'), $request->validated('lang'));

        return response()->noContent();
    }

    /**
     * 查看一次待完成的重置
     *
     * 重置页用它显示「正在为哪个账号设置新密码」。token 不存在或已过期一律 404。
     *
     * @unauthenticated
     *
     * @urlParam token string required 邮件链接里的 token
     *
     * @responseStatus 404 token 无效或已过期
     */
    public function show(string $token): PasswordResetResource
    {
        return PasswordResetResource::make($this->resets->find($token));
    }

    /**
     * 设置新密码
     *
     * 成功后 token 作废，返回 204 空体；之后用新密码走登录。
     *
     * @unauthenticated
     *
     * @urlParam token string required 邮件链接里的 token
     *
     * @bodyParam password string required 新密码，6–32 位
     * @bodyParam password_confirmation string required 再输一次新密码
     *
     * @responseStatus 204 已重置，无响应体
     * @responseStatus 404 token 无效或已过期
     */
    public function update(UpdatePasswordResetRequest $request, string $token)
    {
        $this->resets->reset($token, $request->validated('password'));

        return response()->noContent();
    }
}
