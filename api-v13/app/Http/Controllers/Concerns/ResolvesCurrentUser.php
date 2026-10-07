<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Middleware\V3\Authenticate;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

/**
 * 取当前登录用户——控制器的**胶水**动作，不是业务逻辑。
 *
 * 放在 trait 里而不是每个控制器各写一个 private 方法：控制器文件里除了入口方法
 * 不该再有第二个方法（见 v3-resource skill 硬规范第 5 条）。
 *
 * 挂了 `auth.v3` 中间件的路由，用户已经解好放在 request attributes 里，这里直接取，
 * 不重复解 JWT；没挂中间件的路由（如 tally 那种可选登录）走 {@see self::currentUserUid()}。
 */
trait ResolvesCurrentUser
{
    /**
     * 必须登录。挂了 `auth.v3` 的路由上它不可能抛——中间件已经挡过一道了。
     *
     * @return array{user_uid: string, user_id: int}
     *
     * @throws AuthenticationException 未登录（只会发生在忘挂中间件的路由上）
     */
    protected function currentUser(Request $request): array
    {
        $user = $request->attributes->get(Authenticate::USER);
        if (is_array($user)) {
            return $user;
        }

        throw new AuthenticationException;
    }

    /**
     * 可选登录：取到就返回 uuid，没登录返回 null，不抛异常。
     *
     * 与中间件一致只认 bearer——`AuthService::current()` 会回落去读 cookie，
     * 那是 v2 的旁路，v3 不走。
     */
    protected function currentUserUid(Request $request): ?string
    {
        if ($user = $request->attributes->get(Authenticate::USER)) {
            return $user['user_uid'];
        }
        if (! $request->bearerToken()) {
            return null;
        }

        return AuthService::current($request)['user_uid'] ?? null;
    }
}
