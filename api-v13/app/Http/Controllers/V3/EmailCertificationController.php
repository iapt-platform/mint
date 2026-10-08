<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreEmailCertificationRequest;
use App\Services\V3\SignUpService;

/**
 * 注册邮箱验证码。
 *
 * 取代 `POST /v2/email-certification`（`EmailCertificationController@store`）。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。v2 的
 * `GET /v2/email-certification/{id}` 会把验证码交给前端比对，v3 没有对应端点——
 * 比对改在服务端做，见 `POST /v3/invites`。
 */
class EmailCertificationController extends Controller
{
    public function __construct(private readonly SignUpService $signUp) {}

    /**
     * 发送注册验证码
     *
     * 向邮箱发一个 6 位验证码，30 分钟内有效；重复申请会作废上一个。
     * 邮箱已注册时返回 422。按 IP 与邮箱限流，超出返回 429。
     *
     * @unauthenticated
     *
     * @bodyParam email string required 待注册的邮箱。Example: someone@example.com
     * @bodyParam lang string 邮件语言，没有对应模板的回落 en。Enum: en,en-US,zh-Hans,zh-Hant
     *
     * @responseStatus 204 已发送，无响应体
     * @responseStatus 429 请求过于频繁
     * @responseStatus 503 邮件发送失败
     */
    public function store(StoreEmailCertificationRequest $request)
    {
        $this->signUp->sendCode($request->validated('email'), $request->validated('lang'));

        return response()->noContent();
    }
}
