<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Models\AiModel;
use App\Models\Article;
use App\Models\Attachment;
use App\Models\Channel;
use App\Models\Collection;
use App\Models\Commentary;
use App\Models\Course;
use App\Models\CustomBook;
use App\Models\CustomBookSentence;
use App\Models\DhammaTerm;
use App\Models\DictInfo;
use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Models\Project;
use App\Models\SentBlock;
use App\Models\Share;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\Task;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * 团队本身：搜公开团队、看详情、建、改、删。
 *
 * 取代 v2 `GroupController`（index 的 view=all、show、store、update、destroy）。
 * 「我的团队」列表在 {@see GroupMemberService::listForUser()}（载荷是成员记录）。
 * v2 那份原地保留不动（铁律 2），两边不共用代码。
 */
class GroupService
{
    /**
     * 可能以团队为 owner 的表：删团队前逐表检查，名下还有东西就拒绝。
     * 只列 owner 列存 uuid 的；articles / collections 的 owner_id 是整数用户 id，不算。
     *
     * @var array<string, array{0: class-string, 1: string}>
     */
    private const OWNED = [
        'channel' => [Channel::class, 'owner_uid'],
        'article' => [Article::class, 'owner'],
        'collection' => [Collection::class, 'owner'],
        'term' => [DhammaTerm::class, 'owner'],
        'course' => [Course::class, 'studio_id'],
        'project' => [Project::class, 'owner_id'],
        'task' => [Task::class, 'owner_id'],
        'tag' => [Tag::class, 'owner_id'],
        'tag_map' => [TagMap::class, 'owner_uid'],
        'attachment' => [Attachment::class, 'owner_uid'],
        'ai_model' => [AiModel::class, 'owner_id'],
        'custom_book' => [CustomBook::class, 'owner'],
        'custom_book_sentence' => [CustomBookSentence::class, 'owner'],
        'dict' => [DictInfo::class, 'owner_id'],
        'commentary' => [Commentary::class, 'owner_id'],
        'sent_block' => [SentBlock::class, 'owner_uid'],
    ];

    public function __construct(private readonly GroupAbilityService $abilities) {}

    /**
     * 按名字搜公开团队（共享资源时挑团队用）。私有团队不出现。
     *
     * @return LengthAwarePaginator<int, GroupInfo>
     */
    public function searchPublic(?string $search, int $perPage): LengthAwarePaginator
    {
        return $this->withCreator()
            ->where('status', '>=', GroupInfo::STATUS_PUBLIC)
            ->when($search, fn (Builder $query, string $value) => $query->where('name', 'ilike', '%'.$value.'%'))
            ->orderBy('name')
            ->orderBy('uid')
            ->paginate($perPage);
    }

    /**
     * 团队详情：公开团队谁都能看，私有团队只有成员能看。附上查看者的角色。
     *
     * @throws AuthorizationException 私有团队且不是成员
     */
    public function show(GroupInfo $group, ?string $viewerUid): GroupInfo
    {
        $role = $viewerUid ? $this->abilities->roleIn($viewerUid, $group->uid) : null;
        if ($role === null && $group->status < GroupInfo::STATUS_PUBLIC) {
            throw new AuthorizationException(__('messages.group_not_member'));
        }

        return $this->withViewerRole($group->load(UserService::eagerLoadActor('creator')[0]), $role);
    }

    /**
     * 建团队，建的人成为 owner。同一个人名下不能重名。
     *
     * @param  array{name: string, description?: string|null, status?: int}  $data
     *
     * @throws ValidationException 重名
     */
    public function create(string $userUid, array $data): GroupInfo
    {
        $this->guardUniqueName($userUid, $data['name']);

        $group = DB::transaction(function () use ($userUid, $data) {
            $now = time() * 1000;
            $group = new GroupInfo;
            $group->forceFill([
                'id' => app('snowflake')->id(),
                'uid' => (string) Str::uuid(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? GroupInfo::STATUS_PUBLIC,
                'owner' => $userUid,
                'create_time' => $now,
                'modify_time' => $now,
            ])->save();

            (new GroupMember)->forceFill([
                'id' => app('snowflake')->id(),
                'user_id' => $userUid,
                'group_id' => $group->uid,
                'group_name' => $group->name,
                'role' => GroupMember::ROLE_OWNER,
                'power' => GroupMember::powerOf(GroupMember::ROLE_OWNER),
            ])->save();

            return $group;
        });

        return $this->withViewerRole($group->load(UserService::eagerLoadActor('creator')[0]), GroupMember::ROLE_OWNER);
    }

    /**
     * 改团队信息（owner / manager）。只改传了的字段；改名时同步成员记录上冗余的团队名。
     *
     * @param  array{name?: string, description?: string|null, status?: int}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException 重名
     */
    public function update(GroupInfo $group, string $actorUid, array $data): GroupInfo
    {
        $role = $this->authorize($group, $actorUid, GroupAbilityService::MANAGE_GROUP);
        if (isset($data['name']) && $data['name'] !== $group->name) {
            $this->guardUniqueName($group->owner, $data['name'], $group->uid);
        }

        DB::transaction(function () use ($group, $data) {
            $group->forceFill($data + ['modify_time' => time() * 1000])->save();
            if (isset($data['name'])) {
                $group->members()->update(['group_name' => $data['name']]);
            }
        });

        return $this->withViewerRole($group->load(UserService::eagerLoadActor('creator')[0]), $role);
    }

    /**
     * 删团队（只有 owner）。名下还有资源就拒绝；否则连同成员记录与共享给它的 share 一起硬删。
     *
     * @throws AuthorizationException
     * @throws BusinessException 名下还有资源（409）
     */
    public function delete(GroupInfo $group, string $actorUid): void
    {
        $this->authorize($group, $actorUid, GroupAbilityService::DELETE_GROUP);

        $owned = $this->ownedResources($group->uid);
        if ($owned !== []) {
            throw new BusinessException(
                __('messages.group_has_resources'),
                409,
                'group-has-resources',
                ['resources' => $owned],
            );
        }

        DB::transaction(function () use ($group) {
            $group->shares()->delete();
            $group->members()->delete();
            $group->delete();
        });
    }

    /**
     * 团队名下各类资源的条数，只列有的。
     *
     * @return array<string, int>
     */
    public function ownedResources(string $groupUid): array
    {
        $counts = [];
        foreach (self::OWNED as $type => [$model, $column]) {
            $count = $model::query()->where($column, $groupUid)->count();
            if ($count > 0) {
                $counts[$type] = $count;
            }
        }

        return $counts;
    }

    /**
     * @return Builder<GroupInfo>
     */
    private function withCreator(): Builder
    {
        return GroupInfo::query()->with(UserService::eagerLoadActor('creator')[0]);
    }

    /**
     * 查看者的角色挂在模型上给 Resource 读，Resource 不查库。
     */
    private function withViewerRole(GroupInfo $group, ?string $role): GroupInfo
    {
        $group->setAttribute('viewer_role', $role);

        return $group;
    }

    /**
     * @throws AuthorizationException
     */
    private function authorize(GroupInfo $group, string $actorUid, string $ability): string
    {
        $role = $this->abilities->roleIn($actorUid, $group->uid);
        if (! GroupAbilityService::roleAllows($role, $ability)) {
            throw new AuthorizationException(__('messages.group_forbidden'));
        }

        return $role;
    }

    /**
     * @throws ValidationException
     */
    private function guardUniqueName(string $ownerUid, string $name, ?string $exceptUid = null): void
    {
        $exists = GroupInfo::where('owner', $ownerUid)
            ->where('name', $name)
            ->when($exceptUid, fn (Builder $query, string $uid) => $query->where('uid', '<>', $uid))
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['name' => __('messages.group_name_exists')]);
        }
    }
}
