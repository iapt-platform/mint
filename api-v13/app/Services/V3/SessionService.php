<?php

namespace App\Services\V3;

use App\Models\UserInfo;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

/**
 * 登录与「我是谁」。
 *
 * 与 v2 的 `AuthController@signIn` / `@getUserInfoByToken` 不共用代码（CLAUDE.md 铁律 2）。
 * 签 token 用共享的 `AuthService::getUserToken()`，v2 / v3 签出的 token 完全一样，
 * 两边互认——v4 登录拿到的 token 在 v3 也能用，反之亦然。
 *
 * 与 v2 的区别：
 * - 登录一次就返回 token 与用户信息，前端不必再调一次 `auth/current`。
 * - 邮箱登录不区分大小写；用户名仍区分（与 user_infos 的唯一索引一致）。
 * - 密码比对走 {@see PasswordHasher::check()}（hash_equals）；v2 在 SQL 里比 md5。
 * - 登录失败统一 422 `errors.login`，不区分「没这个人」与「密码错」。
 */
class SessionService
{
    public function __construct(
        private readonly PasswordHasher $hasher,
        private readonly UserService $users,
    ) {}

    /**
     * 用用户名或邮箱 + 密码登录。
     *
     * @return array{token: string, user: array<string, mixed>}
     *
     * @throws ValidationException 账号或密码不对
     */
    public function signIn(string $login, string $password): array
    {
        $user = UserInfo::where('username', $login)
            ->orWhereRaw('lower(email) = ?', [mb_strtolower($login)])
            ->first();

        if (! $user || ! $this->hasher->check($password, (string) $user->password)) {
            throw ValidationException::withMessages(['login' => __('auth.failed')]);
        }

        $token = AuthService::getUserToken($user->userid);
        if (! $token) {
            // getUserToken 只在查无此人时返回 null，而上面刚查到——走到这里是数据不一致
            abort(500);
        }

        return [
            'token' => $token,
            'user' => $this->profile($user),
        ];
    }

    /**
     * 当前登录用户的资料。token 有效但账号已不存在（被删）时 401。
     *
     * @return array<string, mixed>
     */
    public function me(string $userUid): array
    {
        $user = UserInfo::where('userid', $userUid)->first();
        if (! $user) {
            abort(401);
        }

        return $this->profile($user);
    }

    /**
     * 公开摘要（与 v3 其他资源里的 user 同形）+ 本人才需要的补充：
     * roles 恒为数组（没有就是 []），root 管理员固定是 ['root']——与 v2 auth/current 一致。
     *
     * @return array<string, mixed>
     */
    private function profile(UserInfo $user): array
    {
        $profile = $this->users->profile($user);
        $profile['roles'] = $user->userid === config('mint.admin.root_uuid')
            ? ['root']
            : (is_array($profile['roles'] ?? null) ? $profile['roles'] : []);
        $profile['avatar'] ??= null;
        $profile['email'] = $user->email;

        return $profile;
    }
}
