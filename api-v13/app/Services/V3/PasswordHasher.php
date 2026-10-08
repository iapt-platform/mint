<?php

namespace App\Services\V3;

/**
 * v3 写入 `user_infos.password` 的唯一出口。
 *
 * 现在仍是 md5：v2 的 `POST /v2/sign-in` 在 SQL 里比对 `md5(password)`，
 * dashboard-v4 与 wikipali-mobile 都还在用它登录，存成别的格式它们就登不上了。
 *
 * TODO: v4 下线后换 bcrypt（`Hash::make()`），只改这一处。登录端点届时要能识别
 * 旧的 md5 值并就地 rehash，见根目录 CLAUDE.md「认证迁移待办」。
 */
class PasswordHasher
{
    public function hash(string $password): string
    {
        return md5($password);
    }
}
