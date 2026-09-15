<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 运维接口鉴权中间件
 *
 * 从请求头 Authorization: Bearer <token> 中解析 token，
 * 与 config('mint.app.ops_token')（.env 中的 APP_OPS_TOKEN）比对，
 * 不匹配时返回 403。
 */
class OpsToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('mint.app.ops_token');
        $token = (string) $request->bearerToken();

        if ($expected === '' || $token === '' || ! hash_equals($expected, $token)) {
            return response()->json([
                'ok' => false,
                'data' => null,
                'message' => 'Forbidden',
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        return $next($request);
    }
}
