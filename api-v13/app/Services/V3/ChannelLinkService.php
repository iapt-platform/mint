<?php

namespace App\Services\V3;

use App\Models\Channel;
use App\Models\ChannelLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * channel 之间的关系设置（`channel_links` 表）：继承自、降级、捆绑的逐词、术语路径。
 *
 * 服务端只记录，不在任何读取路径上使用它们——降级怎么拼接、术语按什么顺序取，由客户端决定。
 * 新能力，没有对应的 v2 端点。
 *
 * - 读：能读这个 channel 的人都能看；指向的 channel 若已删除或查看者看不见，就不出现
 * - 写：能改这个 channel 信息的人（{@see ChannelAbilityService} 的 update）；AI 模型不能写
 */
class ChannelLinkService
{
    private const TRANSLATION = 'translation';

    private const TARGET_COLUMNS = ['uid', 'name', 'type', 'lang', 'status', 'is_system', 'owner_uid'];

    public function __construct(
        private readonly ChannelAbilityService $abilities,
        private readonly AgentGrantService $agents,
    ) {}

    /**
     * 一个 channel 的全部关系设置。
     *
     * @return array{channel_id: string, derived_from: Channel|null, wbw: Channel|null, fallback: list<Channel>, term_source: list<Channel>, can_update: bool}
     *
     * @throws AuthorizationException 看不见这个 channel
     */
    public function get(Channel $channel, ?string $viewerUid): array
    {
        $abilities = $this->abilities->abilities($channel, $viewerUid);
        if (! $abilities['read']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }

        $links = ChannelLink::where('channel_uid', $channel->uid)
            ->with(['target' => fn ($q) => $q->select(self::TARGET_COLUMNS)])
            ->orderBy('kind')
            ->orderBy('position')
            ->get()
            ->filter(fn (ChannelLink $link) => $link->target !== null);   // 指向的 channel 已软删

        $readable = $this->abilities->abilitiesMany($links->pluck('target')->unique('uid'), $viewerUid);
        $visible = $links->filter(fn (ChannelLink $link) => $readable[$link->target_uid]['read'] ?? false);

        return [
            'channel_id' => $channel->uid,
            'derived_from' => $this->targetsOf($visible, ChannelLink::DERIVED_FROM)->first(),
            'wbw' => $this->targetsOf($visible, ChannelLink::WBW)->first(),
            'fallback' => $this->targetsOf($visible, ChannelLink::FALLBACK)->all(),
            'term_source' => $this->targetsOf($visible, ChannelLink::TERM_SOURCE)->all(),
            'can_update' => $abilities['update'],
        ];
    }

    /**
     * 改关系设置：传了哪个键就整体替换哪一种（单值传 null = 清掉，列表传 [] = 清空），没传的不动。
     *
     * @param  array{derived_from?: string|null, wbw?: string|null, fallback?: list<string>, term_source?: list<string>}  $data
     * @return array{channel_id: string, derived_from: Channel|null, wbw: Channel|null, fallback: list<Channel>, term_source: list<Channel>, can_update: bool}
     *
     * @throws AuthorizationException 不能改这个 channel，或是 AI 模型
     * @throws ValidationException 指向自己、指向看不见 / 不存在的 channel、逐词目标类型不对
     */
    public function update(Channel $channel, string $actorUid, array $data): array
    {
        $this->agents->assertHuman($actorUid);
        if (! $this->abilities->abilities($channel, $actorUid)['update']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }

        $wanted = $this->normalize($data);
        $this->guardTargets($channel, $actorUid, $wanted);

        DB::transaction(function () use ($channel, $wanted) {
            foreach ($wanted as $kind => $targets) {
                ChannelLink::where('channel_uid', $channel->uid)->where('kind', $kind)->delete();
                foreach ($targets as $position => $target) {
                    ChannelLink::create([
                        'channel_uid' => $channel->uid,
                        'kind' => $kind,
                        'target_uid' => $target,
                        'position' => $position,
                    ]);
                }
            }
        });

        return $this->get($channel, $actorUid);
    }

    /**
     * 入参 → kind => 有序的目标 uid 列表（单值的 kind 最多一个）。
     *
     * @param  array<string, mixed>  $data
     * @return array<string, list<string>>
     */
    private function normalize(array $data): array
    {
        $wanted = [];
        foreach (ChannelLink::SINGLE as $kind) {
            if (array_key_exists($kind, $data)) {
                $wanted[$kind] = $data[$kind] === null ? [] : [(string) $data[$kind]];
            }
        }
        foreach (array_keys(ChannelLink::LISTS) as $kind) {
            if (array_key_exists($kind, $data)) {
                $wanted[$kind] = array_values(array_map('strval', $data[$kind] ?? []));
            }
        }

        return $wanted;
    }

    /**
     * 目标必须存在（未删除）、设置人看得见、不是自己。按类型还有三条：
     * - 捆绑的逐词必须是 wbw 类型
     * - 降级版本必须与本 channel 同类型（缺段时拿来顶替，类型不同就接不上）
     * - 术语路径只对 translation channel 有效，目标也只能是 translation（清空总是允许的）
     *
     * 错误挂在具体的字段上。
     *
     * @param  array<string, list<string>>  $wanted
     *
     * @throws ValidationException
     */
    private function guardTargets(Channel $channel, string $actorUid, array $wanted): void
    {
        $uids = array_values(array_unique(array_merge(...array_values($wanted ?: [[]]))));
        $targets = $uids === []
            ? collect()
            : Channel::whereIn('uid', $uids)->get(self::TARGET_COLUMNS)->keyBy('uid');
        $readable = $this->abilities->abilitiesMany($targets->values(), $actorUid);

        $errors = [];
        if (! empty($wanted[ChannelLink::TERM_SOURCE]) && $channel->type !== self::TRANSLATION) {
            $errors[ChannelLink::TERM_SOURCE] = __('messages.channel_link_term_source_channel');
            unset($wanted[ChannelLink::TERM_SOURCE]);
        }
        foreach ($wanted as $kind => $list) {
            foreach ($list as $i => $uid) {
                $field = in_array($kind, ChannelLink::SINGLE, true) ? $kind : "{$kind}.{$i}";
                $target = $targets->get($uid);
                if ($uid === $channel->uid) {
                    $errors[$field] = __('messages.channel_link_self');
                } elseif ($target === null || ! ($readable[$uid]['read'] ?? false)) {
                    $errors[$field] = __('messages.channel_link_target_missing');
                } elseif ($kind === ChannelLink::WBW && $target->type !== 'wbw') {
                    $errors[$field] = __('messages.channel_link_wbw_type');
                } elseif ($kind === ChannelLink::FALLBACK && $target->type !== $channel->type) {
                    $errors[$field] = __('messages.channel_link_fallback_type');
                } elseif ($kind === ChannelLink::TERM_SOURCE && $target->type !== self::TRANSLATION) {
                    $errors[$field] = __('messages.channel_link_term_source_type');
                }
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  Collection<int, ChannelLink>  $links
     * @return Collection<int, Channel>
     */
    private function targetsOf(Collection $links, string $kind): Collection
    {
        return $links->where('kind', $kind)->sortBy('position')->map(fn (ChannelLink $link) => $link->target)->values();
    }
}
