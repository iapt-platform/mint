<?php

namespace App\Services;

use App\Http\Api\UserApi;
use App\Http\Api\AiAssistantApi;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    public static function getUserToken(string $userUid)
    {
        $user = UserApi::getByUuid($userUid);
        if (!$user) {
            $user = AiAssistantApi::getByUuid($userUid);
        }
        if ($user) {
            $ExpTime = time() + 60 * 60 * 24 * 365;
            $key = self::getJwtKey();
            $payload = [
                'nbf' => time(),
                'exp' => $ExpTime,
                'uid' => $user['id'],
                'id' => $user['sn'],
            ];
            $jwt = JWT::encode($payload, $key, 'HS512');
            return $jwt;
        }
        return null;
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
                //过期
                return false;
            } else {
                //有效的token
                return ['user_uid' => $jwt->uid, 'user_id' => $jwt->id];
            }
        } else if (isset($_COOKIE['user_uid'])) {
            return [
                'user_uid' => $_COOKIE['user_uid'],
                'user_id' => $_COOKIE['user_id']
            ];
        } else {
            return false;
        }
    }
}
