<?php

namespace App\Http\Middleware\V3;

use App\Services\AuthService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * v3 的登录闸：没带有效 bearer token 就在进控制器之前 401。
 *
 * 挂法见 routes/v3/*.php，别名 `auth.v3`（在 bootstrap/app.php 注册）。
 * 可以挂在路由组上，也可以按方法挂：
 *
 *     Route::apiResource('channels', ChannelController::class)
 *         ->middlewareFor(['store', 'update', 'destroy'], 'auth.v3');
 *
 * **鉴权和 URL 形状是两件事**：要让某些动作需要登录，挂中间件就行，
 * 不需要把它们塞进 `/v3/me/` 前缀。
 *
 * 两个设计点：
 *
 * 1. **只认 bearer。** `AuthService::current()` 拿不到 bearer 时会回落去读
 *    `$_COOKIE['user_uid']`，那是 v2 的旁路；v3 硬规范要求统一 bearer，所以这里
 *    先自己挡一道。不改 `AuthService`——它是 v2 也在用的共享代码。
 * 2. **解出来的用户存进 request attributes**，控制器直接取，不必再解一次 JWT。
 */
class Authenticate
{
    /**
     * 解析出的用户存在 request attributes 的哪个键下。
     */
    public const USER = 'v3.user';

    /**
     * @throws AuthenticationException 没带 bearer，或 token 无效 / 过期
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 不给 AuthenticationException 传消息：bootstrap/app.php 的处理器只按状态码
        // 渲染，detail 留空，面向人的文案就是 Problem 的 title（RFC 9457 要求它稳定）
        if (! $request->bearerToken()) {
            throw new AuthenticationException;
        }
        $user = AuthService::current($request);
        if (! $user) {
            throw new AuthenticationException;
        }

        $request->attributes->set(self::USER, $user);

        return $next($request);
    }
}
