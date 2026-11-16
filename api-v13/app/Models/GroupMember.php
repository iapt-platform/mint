<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 团队（group）成员。表 `group_members`。
 *
 * 角色有两列：v2 的 `power`（0 owner / 1 manager / 2 member）与 v3 的 `role`
 * （owner / manager / editor / reader）。v2 只会插入、从不改 power；v3 改角色时两列同写。
 * 读角色一律走 {@see self::effectiveRole()}。
 */
class GroupMember extends Model
{
    use HasFactory;

    public const ROLE_OWNER = 'owner';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_READER = 'reader';

    /** v3 可以分配的角色；owner 只在建团队时产生，不能经改角色得到 */
    public const ASSIGNABLE_ROLES = [self::ROLE_MANAGER, self::ROLE_EDITOR, self::ROLE_READER];

    protected $primaryKey = 'id';

    protected $casts = [
        'id' => 'string',
    ];

    /**
     * 实际角色：`role` 有值就用它，为空按 v2 的 `power` 推导。
     *
     * TODO v4 下线后：按 power 回填 role、列改 NOT NULL，删掉这段推导直接读 role。
     */
    public function effectiveRole(): string
    {
        if ($this->role) {
            return $this->role;
        }

        return match ((int) $this->power) {
            0 => self::ROLE_OWNER,
            1 => self::ROLE_MANAGER,
            default => self::ROLE_READER,
        };
    }

    /**
     * 与角色对应的 v2 `power`，v3 改角色时同写，让 v4 的显示与删除权限保持一致。
     */
    public static function powerOf(string $role): int
    {
        return match ($role) {
            self::ROLE_OWNER => 0,
            self::ROLE_MANAGER => 1,
            default => 2,
        };
    }

    /**
     * 成员本人。`user_id` 是 uuid，对 `user_infos.userid`。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'userid');
    }

    /**
     * 所属团队。`group_id` 对 `group_infos.uid`。
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'group_id', 'uid');
    }
}
