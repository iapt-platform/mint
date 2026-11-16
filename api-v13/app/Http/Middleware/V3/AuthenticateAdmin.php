<?php

namespace App\Http\Middleware\V3;

use App\Models\UserInfo;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * v3 管理员闸：只放行 root 与 administrator。必须排在 `auth.v3` 之后（用户已解好放在
 * request attributes 里），所以「没登录」是 401、「登录了但不是管理员」是 403。
 *
 * 角色口径与 v2 一致：root 账号（config('mint.admin.root_uuid')）固定是 root；
 * 其余看 user_infos.role（JSON 数组）里有没有 administrator / root。
 *
 * 别名 `auth.v3.admin`（在 bootstrap/app.php 注册）。
 */
class AuthenticateAdmin
{
    public const ROLES = ['root', 'administrator'];

    /**
     * @throws AuthorizationException 不是管理员
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uid = $request->attributes->get(Authenticate::USER)['user_uid'] ?? null;

        if (! $uid || ! $this->isAdmin($uid)) {
            throw new AuthorizationException;
        }

        return $next($request);
    }

    private function isAdmin(string $uid): bool
    {
        if ($uid === config('mint.admin.root_uuid')) {
            return true;
        }
        $roles = json_decode((string) UserInfo::where('userid', $uid)->value('role'), true);

        return is_array($roles) && array_intersect(self::ROLES, $roles) !== [];
    }
}
