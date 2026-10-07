<?php

namespace App\Http\Api;

use App\Services\UserService;

/**
 * **冻结的转发壳，不要往里写逻辑。**
 *
 * 实现在 {@see UserService}——AI 助手与人类用户拼的是同一种摘要，原先分成两个类
 * 只是历史结果，两份代码已经漂出了差异（头像在 testing 环境的处理不一致）。
 *
 * @deprecated 用 App\Services\UserService
 */
class AiAssistantApi
{
    /** @deprecated 用 UserService::assistantByUuid() */
    public static function getByUuid($id)
    {
        return app(UserService::class)->assistantByUuid($id);
    }

    /** @deprecated 用 UserService::assistantProfile() */
    public static function userInfo($user)
    {
        return app(UserService::class)->assistantProfile($user);
    }
}
