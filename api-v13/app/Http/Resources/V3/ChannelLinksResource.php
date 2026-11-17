<?php

namespace App\Http\Resources\V3;

use App\Models\Channel;
use App\Services\V3\ChannelLinkService;
use Illuminate\Http\Request;

/**
 * 一个 channel 的关系设置（`channel_links` 表）。载荷来自 {@see ChannelLinkService::get()}。
 */
class ChannelLinksResource extends BaseResource
{
    /**
     * @return array{
     *     channel_id: string,
     *     derived_from: array{id: string, name: string, type: string, lang: string, status: int}|null,
     *     wbw: array{id: string, name: string, type: string, lang: string, status: int}|null,
     *     fallback: list<array{id: string, name: string, type: string, lang: string, status: int}>,
     *     term_source: list<array{id: string, name: string, type: string, lang: string, status: int}>,
     *     can_update: bool
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'channel_id' => $this->resource['channel_id'],
            'derived_from' => $this->ref($this->resource['derived_from']),
            'wbw' => $this->ref($this->resource['wbw']),
            'fallback' => array_map(fn (Channel $c) => $this->ref($c), $this->resource['fallback']),
            'term_source' => array_map(fn (Channel $c) => $this->ref($c), $this->resource['term_source']),
            'can_update' => $this->resource['can_update'],
        ];
    }

    /**
     * @return array{id: string, name: string, type: string, lang: string, status: int}|null
     */
    private function ref(?Channel $channel): ?array
    {
        return $channel === null ? null : [
            'id' => (string) $channel->uid,
            'name' => (string) $channel->name,
            'type' => (string) $channel->type,
            'lang' => (string) $channel->lang,
            'status' => (int) $channel->status,
        ];
    }
}
