<?php

namespace App\Services\Markup\Resolve;

use App\Models\Channel;
use Illuminate\Support\Str;

/**
 * 渲染期间用到的 channel 的基本信息（uid、名字、类型、语言、拥有者），攒够一批再查。
 */
final class ChannelDirectory
{
    /** @var array<string, Channel|null> */
    private array $channels = [];

    /** @var array<string, true> */
    private array $pending = [];

    /**
     * @param  list<string>  $uids
     */
    public function request(array $uids): void
    {
        foreach ($uids as $uid) {
            if (Str::isUuid($uid) && ! array_key_exists($uid, $this->channels)) {
                $this->pending[$uid] = true;
            }
        }
    }

    public function get(?string $uid): ?Channel
    {
        if ($uid === null || ! Str::isUuid($uid)) {
            return null;
        }
        if (! array_key_exists($uid, $this->channels)) {
            $this->pending[$uid] = true;
        }
        $this->flush();

        return $this->channels[$uid] ?? null;
    }

    private function flush(): void
    {
        if ($this->pending === []) {
            return;
        }
        $uids = array_keys($this->pending);
        $this->pending = [];
        $found = Channel::whereIn('uid', $uids)
            ->get(['uid', 'name', 'type', 'lang', 'owner_uid'])
            ->keyBy(fn (Channel $channel) => (string) $channel->uid);
        foreach ($uids as $uid) {
            $this->channels[$uid] = $found->get($uid);
        }
    }
}
