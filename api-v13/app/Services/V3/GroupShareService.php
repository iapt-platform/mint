<?php

namespace App\Services\V3;

use App\Models\AiModel;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Collection;
use App\Models\GroupInfo;
use App\Models\Project;
use App\Models\Share;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * 团队文档：共享给某个团队的资源（`shares` 表里 cooperator 是这个团队的行）。
 *
 * 取代 v2 `ShareController@index` 的 view=group，以及团队页上经 `DELETE /v2/share/{id}`
 * 把资源移出团队。资源本身的协作者管理（view=res、给人加共享）仍是 v2 share 资源，不在这里。
 *
 * 规则：成员都能看；团队的 owner / manager 或资源的拥有者能把资源移出团队。
 */
class GroupShareService
{
    /** shares.res_type 的编号 → 对外的类型名，以及取标题、拥有者的列 */
    private const TYPES = [
        1 => ['pcs', null, null, null],
        2 => ['channel', Channel::class, 'name', 'owner_uid'],
        3 => ['article', Article::class, 'title', 'owner'],
        4 => ['collection', Collection::class, 'title', 'owner'],
        5 => ['fragment', null, null, null],
        6 => ['workflow', Project::class, 'title', 'owner_id'],
        7 => ['project', Project::class, 'title', 'owner_id'],
        8 => ['ai_model', AiModel::class, 'name', 'owner_id'],
    ];

    public function __construct(
        private readonly GroupAbilityService $abilities,
        private readonly UserService $users,
    ) {}

    /**
     * 团队文档列表，每条附上资源的类型、标题与拥有者（按类型批量取，不逐行查）。
     *
     * @return LengthAwarePaginator<int, Share>
     *
     * @throws AuthorizationException 不是成员
     */
    public function list(GroupInfo $group, string $viewerUid, int $perPage): LengthAwarePaginator
    {
        if (! $this->abilities->membership($viewerUid, $group->uid)) {
            throw new AuthorizationException(__('messages.group_not_member'));
        }

        $page = $group->shares()->orderByDesc('created_at')->orderBy('id')->paginate($perPage);
        $this->attachResources($page->getCollection()->all());

        return $page;
    }

    /**
     * 把资源移出团队：团队的 owner / manager，或资源的拥有者。
     *
     * @throws AuthorizationException
     */
    public function remove(GroupInfo $group, Share $share, string $actorUid): void
    {
        $this->attachResources([$share]);
        $isResourceOwner = ($share->getAttribute('resource')['owner_id'] ?? null) === $actorUid;
        if (! $isResourceOwner) {
            $role = $this->abilities->roleIn($actorUid, $group->uid);
            if (! GroupAbilityService::roleAllows($role, GroupAbilityService::MANAGE_GROUP)) {
                throw new AuthorizationException(__('messages.group_forbidden'));
            }
        }

        $share->delete();
    }

    /**
     * 按资源类型批量取标题与拥有者，挂到每行的 `resource` 属性上给 Resource 读。
     * 资源已被删掉的，title / owner_id / owner 为 null；拥有者是团队（不是人）时 owner 为 null。
     *
     * @param  list<Share>  $shares
     */
    private function attachResources(array $shares): void
    {
        $byType = [];
        foreach ($shares as $share) {
            $byType[(int) $share->res_type][] = $share->res_id;
        }

        $found = [];
        foreach ($byType as $type => $ids) {
            [, $model, $titleColumn, $ownerColumn] = self::TYPES[$type] ?? [null, null, null, null];
            if (! $model) {
                continue;
            }
            foreach ($model::query()->whereIn('uid', array_unique($ids))->get(['uid', $titleColumn, $ownerColumn]) as $row) {
                $found[$type][$row->uid] = ['title' => $row->{$titleColumn}, 'owner_id' => (string) $row->{$ownerColumn}];
            }
        }

        $owners = $this->users->byUuids(array_merge(
            [],
            ...array_map(fn (array $rows) => array_column($rows, 'owner_id'), array_values($found)),
        ));

        foreach ($shares as $share) {
            $type = (int) $share->res_type;
            $ownerId = $found[$type][$share->res_id]['owner_id'] ?? null;
            $share->setAttribute('resource', [
                'type' => self::TYPES[$type][0] ?? 'unknown',
                'id' => $share->res_id,
                'title' => $found[$type][$share->res_id]['title'] ?? null,
                'owner_id' => $ownerId,
                'owner' => $ownerId ? ($owners[$ownerId] ?? null) : null,
            ]);
        }
    }
}
