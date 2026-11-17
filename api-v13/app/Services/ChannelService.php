<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * channel 的纯能力（v2 / v3 共享）：按 uid / 名字取 channel、取系统 channel、建用户自定义书的 channel。
 *
 * 只放与请求、响应形状、权限无关的逻辑。**权限判定不在这里**：
 * v3 用 {@see V3\ChannelAbilityService}（认团队角色与共享），
 * v2 的旧口径（只认拥有者本人 + 共享）留在 `App\Http\Api\ChannelApi`，随 v4 下线删除。
 *
 * 取代 `App\Http\Api\ChannelApi` 的同名逻辑，那边只剩转发壳。
 */
class ChannelService
{
    /**
     * channel 摘要；不是 uuid 或不存在返回 false。
     *
     * `studio_id` 是 owner_uid（v2 的字段名，转发壳要原样返回）。
     *
     * @return array{id: string, name: string, type: string, lang: string, studio_id: string, status: int}|false
     */
    public function summary(?string $uid): array|false
    {
        if (! Str::isUuid($uid)) {
            return false;
        }
        $channel = Channel::where('uid', $uid)->first(['uid', 'name', 'type', 'lang', 'owner_uid', 'status']);

        return $channel ? $this->toSummary($uid, $channel) : false;
    }

    /**
     * 批量取摘要，按传入顺序一一对应；查不到的位置是 false。一次查询。
     *
     * @param  array<int, string|null>  $uids
     * @return list<array{id: string, name: string, type: string, lang: string, studio_id: string, status: int}|false>
     */
    public function summaries(array $uids): array
    {
        $valid = array_values(array_filter($uids, fn ($uid) => Str::isUuid($uid)));
        $found = $valid === []
            ? collect()
            : Channel::whereIn('uid', array_unique($valid))
                ->get(['uid', 'name', 'type', 'lang', 'owner_uid', 'status'])
                ->keyBy(fn (Channel $channel) => (string) $channel->uid);

        $result = [];
        foreach ($uids as $uid) {
            $channel = Str::isUuid($uid) ? $found->get($uid) : null;
            $result[] = $channel ? $this->toSummary($uid, $channel) : false;
        }

        return $result;
    }

    /**
     * 系统 channel（root 账号名下、按名字找）的 uid；找不到时试 $fallback，都没有返回 false。
     */
    public function systemUid(string $name, string $fallback = ''): string|false
    {
        $uid = $this->rootChannelUid($name);
        if ($uid === false && $fallback !== '') {
            $uid = $this->rootChannelUid($fallback);
        }

        return $uid;
    }

    /**
     * 按名字取 channel（不限拥有者，取第一条）。
     *
     * @throws \Exception channel 不存在
     */
    public function byNameOrFail(string $name): Channel
    {
        $channel = Channel::where('name', $name)->first();
        if (! $channel) {
            throw new \Exception('channel is invalid');
        }

        return $channel;
    }

    /**
     * 某个 studio 某个语言的「自定义书」channel（`_user_book_{lang}`）的 uid，没有就建一个。
     * 建失败返回 false。
     */
    public function userBookUid(string $studioUid, string $lang, int $status): string|false
    {
        $name = '_user_book_'.$lang;
        $existing = Channel::where('owner_uid', $studioUid)->where('name', $name)->value('uid');
        if ($existing) {
            return (string) $existing;
        }

        $channel = new Channel;
        $channel->id = app('snowflake')->id();
        $channel->uid = (string) Str::uuid();
        $channel->owner_uid = $studioUid;
        $channel->name = $name;
        $channel->type = 'original';
        $channel->lang = $lang;
        $channel->editor_id = 0;
        $channel->is_system = true;
        $channel->create_time = time() * 1000;
        $channel->modify_time = time() * 1000;
        $channel->status = $status;
        if (! $channel->save()) {
            Log::error('copy user book : create channel fail.', ['channel' => $name, 'studioId' => $studioUid]);

            return false;
        }
        Log::debug('copy user book : create channel success name='.$name);

        return $channel->uid;
    }

    private function rootChannelUid(string $name): string|false
    {
        $uid = Channel::where('name', $name)
            ->where('owner_uid', config('mint.admin.root_uuid'))
            ->value('uid');

        return $uid ? (string) $uid : false;
    }

    /**
     * @return array{id: string, name: string, type: string, lang: string, studio_id: string, status: int}
     */
    private function toSummary(string $uid, Channel $channel): array
    {
        return [
            'id' => $uid,
            'name' => $channel->name,
            'type' => $channel->type,
            'lang' => $channel->lang,
            'studio_id' => $channel->owner_uid,
            'status' => $channel->status,
        ];
    }
}
