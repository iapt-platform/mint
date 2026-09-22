<?php

namespace App\Services;

use App\Http\Api\UserApi;
use App\Models\AiModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * 人类用户登录 token 的有效期（秒）。
     */
    private const USER_TOKEN_TTL = 60 * 60 * 24 * 365;

    /**
     * AI 模型身份 token 的有效期（秒）。
     *
     * 模型 token 会被复制到外部客户端（如 wikipali-write Skill）的凭据文件里，
     * 泄漏面比人类登录 token 大得多，故取远短的 30 天。
     */
    private const AI_MODEL_TOKEN_TTL = 60 * 60 * 24 * 30;

    /**
     * 模型身份 token 的 typ 标记。带此标记的 token 每次校验都要比对版本号，
     * 人类 token 不带，避免为每个请求多查一次库。
     */
    private const AI_MODEL_TOKEN_TYPE = 'ai-model';

    public static function getUserToken(string $userUid)
    {
        // 先判模型：UserApi::getByUuid() 查不到用户时会回落到 AiAssistantApi，
        // 分不清「模型」和「查无此人」，而这两者签出的 token 完全不同。
        $aiModel = AiModel::where('uid', $userUid)->first();
        if ($aiModel) {
            return self::encode([
                'uid' => $aiModel->uid,
                'id' => 0,
                'typ' => self::AI_MODEL_TOKEN_TYPE,
                'ver' => (int) $aiModel->token_version,
            ], self::AI_MODEL_TOKEN_TTL);
        }

        $user = UserApi::getByUuid($userUid);
        if (! $user || ! isset($user['sn'])) {
            // 查无此人时 UserApi 返回的是 id=0 的占位结构，不能拿它签 token
            return null;
        }

        return self::encode([
            'uid' => $user['id'],
            'id' => $user['sn'],
        ], self::USER_TOKEN_TTL);
    }

    public static function getJwtKey()
    {
        return config('mint.app.jwt_secrets_key');
    }

    public static function getToken(Request $request)
    {
        $token = $request->bearerToken();

        return $token;
    }

    public static function current(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            try {

                $jwt = JWT::decode($token, new Key(self::getJwtKey(), 'HS512'));
            } catch (\Exception $e) {
                return false;
            }
            if ($jwt->exp < time()) {
                // 过期
                return false;
            }
            if (! self::modelTokenIsValid($jwt)) {
                return false;
            }

            // 有效的token
            return ['user_uid' => $jwt->uid, 'user_id' => $jwt->id];
        } elseif (isset($_COOKIE['user_uid'])) {
            return [
                'user_uid' => $_COOKIE['user_uid'],
                'user_id' => $_COOKIE['user_id'],
            ];
        } else {
            return false;
        }
    }

    /**
     * 校验模型身份 token 是否已被撤销。
     *
     * 撤销即把 ai_models.token_version 自增，旧 token 里的 ver 随即对不上。
     * 模型被删除同样视为失效。人类 token 直接放行，不查库；唯一的例外是
     * root 管理员（自增主键 id 恰好是 0），见方法内 id === 0 分支的说明。
     */
    private static function modelTokenIsValid(object $jwt): bool
    {
        if (isset($jwt->typ) && $jwt->typ === self::AI_MODEL_TOKEN_TYPE) {
            $version = AiModel::where('uid', $jwt->uid)->value('token_version');

            return $version !== null && (int) $version === (int) ($jwt->ver ?? 0);
        }

        // 人类 token（自增主键 id > 0）直接放行，不查库。
        if ((int) ($jwt->id ?? 0) !== 0) {
            return true;
        }

        // id === 0 的 token 有两种来源，不能再只凭 id 判死：
        //   1. root 管理员的人类 token —— 其自增主键 id 恰好是 0（历史种子数据）；
        //   2. 引入版本号之前签出的旧模型 token（typ 缺失、id 恒为 0）—— 无法撤销。
        // 旧模型 token 的 uid 一定落在 ai_models 表，root 的 uid 不在其中，
        // 故用「uid 是否命中 ai_models」区分：命中 → 旧模型 token，作废；未命中 → 人类，放行。
        // ai_models.uid 是 uuid 列，uid 不是合法 UUID 时查库会报类型错误，先挡掉。
        if (! Str::isUuid((string) $jwt->uid)) {
            return false;
        }

        return ! AiModel::where('uid', $jwt->uid)->exists();
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private static function encode(array $claims, int $ttl): string
    {
        return JWT::encode(array_merge([
            'nbf' => time(),
            'exp' => time() + $ttl,
        ], $claims), self::getJwtKey(), 'HS512');
    }
}
