<?php

namespace App\Services\V3;

use App\Models\GroupMember;

/**
 * 团队空间的权限判定：「某人对某个 owner 名下的资源能做什么」。
 *
 * owner 是本人 → 什么都能做；owner 是一个团队 → 按此人在团队里的角色查表；
 * 其余 → 什么都不能做。资源自己的协作（share 表）不在这里管，由各资源的 Service 叠加。
 *
 * 只给 v3 用。v2 的权限判定（`ShareApi`、`ChecksChannelEditPower` …）只认
 * 「owner 就是本人」，不改它——见 CLAUDE.md 铁律 2。
 */
class GroupAbilityService
{
    /** 看得到 owner 名下的资源 */
    public const READ = 'read';

    /** 在 owner 名下新建资源 */
    public const CREATE = 'create';

    /** 修改 owner 名下的资源（含 channel 里的译文、术语等内容） */
    public const UPDATE = 'update';

    /** 删除 owner 名下的资源 */
    public const DELETE = 'delete';

    /** 加人、移人、改成员角色 */
    public const MANAGE_MEMBERS = 'manage_members';

    /** 改团队本身的名称、简介、公开状态；从团队文档里移除共享 */
    public const MANAGE_GROUP = 'manage_group';

    /** 删除团队本身（区别于删除团队名下的资源） */
    public const DELETE_GROUP = 'delete_group';

    /** @var array<string, list<string>> */
    private const ABILITIES = [
        GroupMember::ROLE_OWNER => [
            self::READ, self::CREATE, self::UPDATE, self::DELETE,
            self::MANAGE_MEMBERS, self::MANAGE_GROUP, self::DELETE_GROUP,
        ],
        GroupMember::ROLE_MANAGER => [self::READ, self::CREATE, self::UPDATE, self::MANAGE_MEMBERS, self::MANAGE_GROUP],
        GroupMember::ROLE_EDITOR => [self::READ, self::UPDATE],
        GroupMember::ROLE_READER => [self::READ],
    ];

    /**
     * 某人对某个 owner（用户或团队的 uuid）能不能做某件事。
     */
    public function can(string $userUid, string $ownerUid, string $ability): bool
    {
        if ($userUid === $ownerUid) {
            return true;
        }

        return self::roleAllows($this->roleIn($userUid, $ownerUid), $ability);
    }

    /**
     * 此人在团队里的角色；不是成员（或 owner 根本不是团队）返回 null。
     */
    public function roleIn(string $userUid, string $groupUid): ?string
    {
        return $this->membership($userUid, $groupUid)?->effectiveRole();
    }

    /**
     * 此人在团队里的成员记录；不是成员返回 null。
     */
    public function membership(string $userUid, string $groupUid): ?GroupMember
    {
        // 不做缓存：控制器实例会被路由缓存、Service 跟着跨请求存活，缓存会让改过的角色迟迟不生效
        return GroupMember::where('group_id', $groupUid)
            ->where('user_id', $userUid)
            ->first();
    }

    /**
     * 角色是否包含某项能力。null（不是成员）一律不包含。
     */
    public static function roleAllows(?string $role, string $ability): bool
    {
        return $role !== null && in_array($ability, self::ABILITIES[$role] ?? [], true);
    }
}
