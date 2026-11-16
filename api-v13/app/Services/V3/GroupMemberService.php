<?php

namespace App\Services\V3;

use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Models\UserInfo;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * 团队成员的业务逻辑：我加入的团队、成员列表、加人、改角色、移人 / 退出。
 *
 * 取代 v2 `GroupMemberController` 的 view=user / view=group / store / destroy。
 * v2 那份原地保留不动（铁律 2），两边不共用代码。
 *
 * 规则：
 * - 看成员列表：任何成员
 * - 加人、移人、改角色：owner 与 manager（{@see GroupAbilityService::MANAGE_MEMBERS}）
 * - owner 那一行谁都不能改、不能移；本人可以退出（owner 除外）
 * - 改角色时 role 与 power 同写，v4 看到的身份与 v3 一致
 */
class GroupMemberService
{
    public function __construct(private readonly GroupAbilityService $abilities) {}

    /**
     * 我加入的团队，按加入时间排序。
     *
     * `owned`：true 只要我创建的，false 只要我参加的别人的团队（取代 v2 的 view2 与
     * `group-my-number`——数量读分页的 total）。`search`：按团队名模糊匹配。
     *
     * @param  array{owned?: bool, search?: string|null}  $filters
     * @return LengthAwarePaginator<int, GroupMember>
     */
    public function listForUser(string $userUid, array $filters, int $perPage): LengthAwarePaginator
    {
        return GroupMember::with([
            'group:uid,name,description,owner,status,created_at',
            'group.'.UserService::eagerLoadActor('creator')[0],
        ])
            ->where('user_id', $userUid)
            ->whereHas('group', function ($query) use ($userUid, $filters) {
                if (array_key_exists('owned', $filters)) {
                    $query->where('owner', $filters['owned'] ? '=' : '<>', $userUid);
                }
                if (! empty($filters['search'])) {
                    $query->where('name', 'ilike', '%'.$filters['search'].'%');
                }
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * 团队的成员列表，只有成员看得到。
     *
     * @return LengthAwarePaginator<int, GroupMember>
     *
     * @throws AuthorizationException 不是成员
     */
    public function listForGroup(GroupInfo $group, string $viewerUid, int $perPage): LengthAwarePaginator
    {
        if (! $this->abilities->membership($viewerUid, $group->uid)) {
            throw new AuthorizationException(__('messages.group_not_member'));
        }

        return $group->members()
            ->with(UserService::eagerLoadActor()[0])
            ->orderBy('power')
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * 加一名成员。
     *
     * @param  array{user_id: string, role?: string}  $data
     *
     * @throws AuthorizationException 调用者不能管理成员
     * @throws ValidationException 用户不存在或已是成员
     */
    public function add(GroupInfo $group, string $actorUid, array $data): GroupMember
    {
        $this->authorizeManage($group, $actorUid);

        if (! UserInfo::where('userid', $data['user_id'])->exists()) {
            throw ValidationException::withMessages(['user_id' => __('messages.group_user_not_found')]);
        }
        if ($group->members()->where('user_id', $data['user_id'])->exists()) {
            throw ValidationException::withMessages(['user_id' => __('messages.group_member_exists')]);
        }

        $role = $data['role'] ?? GroupMember::ROLE_READER;
        $member = new GroupMember;
        $member->forceFill([
            'id' => app('snowflake')->id(),
            'user_id' => $data['user_id'],
            'group_id' => $group->uid,
            'group_name' => $group->name,
            'role' => $role,
            'power' => GroupMember::powerOf($role),
        ])->save();

        return $member->load(UserService::eagerLoadActor()[0]);
    }

    /**
     * 改成员角色。
     *
     * @param  array{role?: string}  $data
     *
     * @throws AuthorizationException 调用者不能管理成员，或目标是 owner
     */
    public function update(GroupInfo $group, GroupMember $member, string $actorUid, array $data): GroupMember
    {
        $this->authorizeManage($group, $actorUid);
        $this->guardOwner($member);

        if (isset($data['role'])) {
            $member->forceFill([
                'role' => $data['role'],
                'power' => GroupMember::powerOf($data['role']),
            ])->save();
        }

        return $member->load(UserService::eagerLoadActor()[0]);
    }

    /**
     * 移除成员，或本人退出。owner 不能被移除、也不能退出。
     *
     * @throws AuthorizationException
     */
    public function remove(GroupInfo $group, GroupMember $member, string $actorUid): void
    {
        $this->guardOwner($member);
        if ($member->user_id !== $actorUid) {
            $this->authorizeManage($group, $actorUid);
        }

        $member->delete();
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizeManage(GroupInfo $group, string $actorUid): void
    {
        $role = $this->abilities->roleIn($actorUid, $group->uid);
        if (! GroupAbilityService::roleAllows($role, GroupAbilityService::MANAGE_MEMBERS)) {
            throw new AuthorizationException(__('messages.group_forbidden'));
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function guardOwner(GroupMember $member): void
    {
        if ($member->effectiveRole() === GroupMember::ROLE_OWNER) {
            throw new AuthorizationException(__('messages.group_owner_immutable'));
        }
    }
}
