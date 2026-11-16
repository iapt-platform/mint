<?php

namespace App\Http\Resources\V3;

use App\Models\Channel;
use Illuminate\Http\Request;

/**
 * 有译文的公开 channel：channel 本身 + `chapter_count`（完成度过门槛的章节数）。
 *
 * @mixin Channel
 */
class TipitakaChannelResource extends ChannelResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     name: string,
     *     summary: string|null,
     *     type: string,
     *     lang: string,
     *     status: int,
     *     is_system: bool,
     *     source_type: string,
     *     source_id: string|null,
     *     owner: array{kind: string, id: string, nickName: string, realName: string, avatar: string|null},
     *     abilities: array{read: bool, write: bool, update: bool, delete: bool},
     *     chapter_count: int,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray($request): array
    {
        return parent::toArray($request) + ['chapter_count' => (int) $this->getAttribute('chapter_count')];
    }
}
