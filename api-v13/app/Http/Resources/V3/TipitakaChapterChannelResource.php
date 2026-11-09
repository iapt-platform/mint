<?php

namespace App\Http\Resources\V3;

use App\Services\V3\TipitakaChapterChannelService;
use Illuminate\Http\Request;

/**
 * 某一章里的一个 channel 及其完成度。
 *
 * 底层是 progress_chapters 一行（join channels）。`views` / `studio` / `progress_line`
 * 由 {@see TipitakaChapterChannelService} 整页批量取好挂在模型上，这里不查库。
 *
 * 类型核对自 information_schema：progress_chapters.uid / channel_id 是 uuid，
 * progress 是 double，book / para 是 integer；channels.type / lang 是 varchar。
 */
class TipitakaChapterChannelResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     book: int,
     *     para: int,
     *     progress: float,
     *     progress_line: array<int, int>|null,
     *     views: int,
     *     updated_at: string,
     *     channel: array{id: string, name: string, type: string, lang: string},
     *     studio: array{id: string, nickName: string, realName: string, avatar?: string}|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->uid,
            'book' => (int) $this->book,
            'para' => (int) $this->para,
            'progress' => (float) $this->progress,
            'progress_line' => is_array($this->progress_line) ? array_values($this->progress_line) : null,
            'views' => (int) $this->views,
            'updated_at' => $this->updated_at?->toJSON(),
            'channel' => [
                'id' => (string) $this->channel_id,
                'name' => (string) $this->channel_name,
                'type' => (string) $this->channel_type,
                'lang' => (string) $this->channel_lang,
            ],
            'studio' => $this->studio,
        ];
    }
}
