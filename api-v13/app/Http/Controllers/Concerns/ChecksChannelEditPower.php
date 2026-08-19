<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Api\ShareApi;
use App\Models\AccessToken;
use App\Models\Channel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * channel 编辑权的统一判定：owner → 协作者 → access token。
 *
 * 第三条分支是外部客户端（如 wikipali write Skill）唯一的入口：AI 模型
 * 有自己的 uid，既不是 owner 也不在 share 表里，只能靠人类用户为它签出的
 * access token 代持权限。
 */
trait ChecksChannelEditPower
{
    /**
     * @param  string  $userId  当前身份的 uuid（人类用户或 AI 模型）
     * @param  int  $book  本次写入涉及的 book；无 book 概念的资源传 0
     * @param  string|null  $accessToken  由 AccessTokenController 签出的 JWT
     */
    protected function userCanEditChannel(string $userId, string $channelId, int $book, $accessToken = null): bool
    {
        $channel = Channel::where('uid', $channelId)->first();
        if (! $channel) {
            return false;
        }
        if ($channel->owner_uid !== $userId) {
            // 判断是否为协作
            $power = ShareApi::getResPower($userId, $channel->uid, 2);
            if ($power < 20) {
                // 判断token
                if (! $accessToken) {
                    return false;
                }
                $key = AccessToken::where('res_id', $channelId)->value('token');
                if (! $key) {
                    return false;
                }
                try {
                    // access token 现在带 exp，过期会抛 ExpiredException；
                    // 伪造/损坏的 token 同样抛异常。一律当作无权，不要冒泡成 500。
                    $jwt = JWT::decode($accessToken, new Key($key.$key, 'HS512'));
                } catch (\Exception $e) {
                    return false;
                }
                if (isset($jwt->book) && $jwt->book !== 0 && $jwt->book !== $book) {
                    return false;
                }
            }
        }

        return true;
    }
}
