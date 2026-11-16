<?php

namespace App\Services\V3;

use App\Models\Channel;
use App\Models\GroupMember;
use App\Models\Share;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Str;

/**
 * channel 的权限判定（只给 v3 用）：谁看得见、谁能写内容、谁能改信息、谁能删。
 *
 * 三种来源叠加：
 * 1. **拥有者**：owner 是本人 → 什么都能做
 * 2. **团队角色**：owner 是团队 → 按本人在团队里的角色（{@see GroupAbilityService}）：
 *    reader 读；editor 读、写内容、改信息；manager 同 editor（另能在团队名下新建）；owner 还能删
 * 3. **共享**（shares 表，直接共享给本人或本人所在的团队）：power 10 读、20 写内容、30 改信息
 *
 * 「能访问」与「会被列出」是两回事：
 * - **能访问**（{@see self::abilities()} 的 read）：status ≥ 20 谁都能读——30 公开，20 公开不列出
 *   （拿到链接就能看）。系统 channel（原文、系统术语等全站共用的基础数据）不论 status 也谁都能读——
 *   库里一半系统 channel 是 status 10，巴利原文 `_System_Pali_VRI_` 就是。
 * - **会被列出**（{@see self::scopeListable()}）：给别人看的列表只列 status 30 与系统 channel；
 *   20 只在拥有者、团队成员、被共享的人自己的列表里出现。
 * 系统 channel 不能改信息、不能删。
 * v2 的判定（`ShareApi`、`ChecksChannelEditPower` …）只认「owner 是本人」，不改它（铁律 2）。
 */
class ChannelAbilityService
{
    public const SHARE_CHANNEL = 2;

    public const POWER_READ = 10;

    public const POWER_WRITE = 20;

    public const POWER_MANAGE = 30;

    /** 能写内容的团队角色 */
    private const WRITER_ROLES = [GroupMember::ROLE_OWNER, GroupMember::ROLE_MANAGER, GroupMember::ROLE_EDITOR];

    public function __construct(private readonly GroupAbilityService $groups) {}

    /**
     * 本人对一个 channel 能做什么。
     *
     * @return array{read: bool, write: bool, update: bool, delete: bool}
     */
    public function abilities(Channel $channel, ?string $userUid): array
    {
        return $this->abilitiesMany([$channel], $userUid)[$channel->uid];
    }

    /**
     * 整页批量算：团队角色一次、共享权限一次，查询数与行数无关。
     *
     * @param  iterable<Channel>  $channels
     * @return array<string, array{read: bool, write: bool, update: bool, delete: bool}>
     */
    public function abilitiesMany(iterable $channels, ?string $userUid): array
    {
        $teams = $userUid ? $this->teamRoles($userUid) : [];
        $powers = [];
        if ($userUid) {
            $ids = [];
            foreach ($channels as $channel) {
                $ids[] = $channel->uid;
            }
            $powers = Share::whereIn('res_id', $ids)
                ->where('res_type', self::SHARE_CHANNEL)
                ->whereIn('cooperator_id', [$userUid, ...array_keys($teams)])
                ->selectRaw('res_id, max(power) as power')
                ->groupBy('res_id')
                ->pluck('power', 'res_id')
                ->all();
        }

        $result = [];
        foreach ($channels as $channel) {
            $read = (int) $channel->status >= Channel::STATUS_UNLISTED || (bool) $channel->is_system;
            $write = $update = $delete = false;
            if ($userUid !== null) {
                if ($channel->owner_uid === $userUid) {
                    $write = $update = $delete = true;
                } else {
                    $role = $teams[$channel->owner_uid] ?? null;
                    $power = (int) ($powers[$channel->uid] ?? 0);
                    $write = in_array($role, self::WRITER_ROLES, true) || $power >= self::POWER_WRITE;
                    $update = GroupAbilityService::roleAllows($role, GroupAbilityService::UPDATE) || $power >= self::POWER_MANAGE;
                    $delete = GroupAbilityService::roleAllows($role, GroupAbilityService::DELETE);
                    $read = $read || $role !== null || $power >= self::POWER_READ;
                }
            }
            if ($channel->is_system) {
                $update = $delete = false;
            }
            $result[$channel->uid] = ['read' => $read || $write, 'write' => $write, 'update' => $update, 'delete' => $delete];
        }

        return $result;
    }

    /**
     * 读正文前的闸：channel 不存在（或已软删）404，看不见 403。
     *
     * 给按 channel 取正文的接口用（`tipitaka-reading`、`search/{id}` 的整章文档）：
     * 私有 channel 只有拥有者、团队成员、被共享的人能读；公开与「公开不列出」谁都能读。
     *
     * @throws AuthorizationException
     */
    public function assertReadable(string $channelUid, ?string $userUid): void
    {
        $channel = Channel::query()
            ->where('uid', $channelUid)
            ->firstOrFail(['uid', 'owner_uid', 'status', 'is_system']);
        if (! $this->abilities($channel, $userUid)['read']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }
    }

    /**
     * 能不能在某个 studio（本人或团队）名下新建 channel。
     */
    public function canCreateIn(string $userUid, string $studioUid): bool
    {
        return $this->groups->can($userUid, $studioUid, GroupAbilityService::CREATE);
    }

    /**
     * 能不能看某个 studio（本人或团队）的工作区列表：本人，或该团队的任何成员。
     */
    public function canListStudio(string $userUid, string $studioUid): bool
    {
        return $this->groups->can($userUid, $studioUid, GroupAbilityService::READ);
    }

    /**
     * 查询约束：列表里列得出来的 channel——公开的（status 30）、系统的，加上（登录时）本人的、
     * 本人所在团队名下的、共享给本人或本人所在团队的。status 20（公开不列出）只在后三种里出现。
     *
     * @param  string  $table  channels 表在查询里的名字（join 时可能带别名）
     */
    public function scopeListable(Builder $query, ?string $userUid, string $table = 'channels'): void
    {
        $query->where(function (Builder $q) use ($userUid, $table) {
            $q->where("{$table}.status", '>=', Channel::STATUS_PUBLIC)
                ->orWhere("{$table}.is_system", true);
            if ($userUid === null) {
                return;
            }
            $teams = $this->teamRoles($userUid);
            $q->orWhere("{$table}.owner_uid", $userUid);
            if ($teams !== []) {
                $q->orWhereIn("{$table}.owner_uid", array_keys($teams));
            }
            $shared = $this->sharedChannels([$userUid, ...array_keys($teams)], self::POWER_READ);
            if ($shared !== []) {
                $q->orWhereIn("{$table}.uid", $shared);
            }
        });
    }

    /**
     * 查询约束：能写内容的 channel（本人的、本人是团队 editor 及以上的团队名下的、共享权限 ≥20 的）。
     */
    public function scopeWritable(Builder $query, string $userUid, string $table = 'channels'): void
    {
        $teams = $this->teamRoles($userUid);
        $writerTeams = array_keys(array_filter($teams, fn (string $role) => in_array($role, self::WRITER_ROLES, true)));
        $shared = $this->sharedChannels([$userUid, ...array_keys($teams)], self::POWER_WRITE);

        $query->where(function (Builder $q) use ($userUid, $table, $writerTeams, $shared) {
            $q->where("{$table}.owner_uid", $userUid);
            if ($writerTeams !== []) {
                $q->orWhereIn("{$table}.owner_uid", $writerTeams);
            }
            if ($shared !== []) {
                $q->orWhereIn("{$table}.uid", $shared);
            }
        });
    }

    /**
     * 直接共享给某个 studio（本人或团队，**不含**经团队转来的）的 channel uid。
     *
     * @return list<string>
     */
    public function sharedTo(string $studioUid): array
    {
        return $this->sharedChannels([$studioUid], self::POWER_READ);
    }

    /**
     * 本人所在的团队 → 角色。
     *
     * @return array<string, string>
     */
    private function teamRoles(string $userUid): array
    {
        $roles = [];
        foreach (GroupMember::where('user_id', $userUid)->get(['group_id', 'role', 'power']) as $member) {
            $roles[$member->group_id] = $member->effectiveRole();
        }

        return $roles;
    }

    /**
     * 共享给这些人 / 团队、且权限不低于 $minPower 的 channel uid。
     *
     * @param  list<string>  $cooperators
     * @return list<string>
     */
    private function sharedChannels(array $cooperators, int $minPower): array
    {
        return Share::whereIn('cooperator_id', $cooperators)
            ->where('res_type', self::SHARE_CHANNEL)
            ->where('power', '>=', $minPower)
            ->pluck('res_id')
            // shares.res_id 是 varchar，channels.uid 是 uuid；非法值进了 where 会让 pgsql 报错
            ->filter(fn ($id) => Str::isUuid($id))
            ->unique()
            ->values()
            ->all();
    }
}
