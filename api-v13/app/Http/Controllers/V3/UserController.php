<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreUserRequest;
use App\Http\Resources\V3\UserResource;
use App\Services\V3\SignUpService;

/**
 * 账号。
 *
 * store 取代 `POST /v2/sign-up`（`SignUpController@store`）。v2 那份在
 * dashboard-v4 下线前原样保留，不要改动。
 */
class UserController extends Controller
{
    public function __construct(private readonly SignUpService $signUp) {}

    /**
     * 注册账号
     *
     * 凭 invite（邀请邮件里的，或 `POST /v3/invites` 换来的）建账号，同时建一个
     * 私有的 draft 译文 channel，invite 标为已用。邮箱取自 invite。
     *
     * 注册不等于登录：返回 201 与新账号，之后用用户名/邮箱 + 密码走登录。
     *
     * @unauthenticated
     *
     * @bodyParam invite string required 注册邀请的 uuid
     * @bodyParam username string required 用户名，6–32 位字母、数字、下划线
     * @bodyParam nickname string 昵称，不传或空白时用 username
     * @bodyParam password string required 密码，6–32 位
     * @bodyParam password_confirmation string required 再输一次密码
     * @bodyParam lang string required 常用译文语言，用作 draft channel 的语言。Example: zh-Hans
     *
     * @responseStatus 201 已创建
     * @responseStatus 429 请求过于频繁
     */
    public function store(StoreUserRequest $request): UserResource
    {
        return UserResource::make($this->signUp->register($request->validated()));
    }
}
