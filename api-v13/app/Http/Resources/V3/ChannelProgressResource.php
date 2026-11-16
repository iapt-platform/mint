<?php

namespace App\Http\Resources\V3;

use App\Models\Channel;
use Illuminate\Http\Request;

/**
 * 一个 channel 在一批句子上的完成度：channel 本身 + `final` + 内容的最早 / 最近时间。
 *
 * `final` 与请求里的句子一一对应：`[原文字符数, 是否已译]`；这个 channel 在这批句子上一句都没译时为 null
 * （它出现在结果里是因为我能写它）。由 Service 挂上，本类不查库。
 *
 * @mixin Channel
 */
class ChannelProgressResource extends ChannelResource
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
     *     final: array|null,
     *     content_created_at: string|null,
     *     content_updated_at: string|null,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray($request): array
    {
        return parent::toArray($request) + [
            'final' => $this->getAttribute('final'),
            'content_created_at' => $this->getAttribute('content_created_at'),
            'content_updated_at' => $this->getAttribute('content_updated_at'),
        ];
    }
}
