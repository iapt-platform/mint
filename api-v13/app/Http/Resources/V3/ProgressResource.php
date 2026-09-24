<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 章节翻译进度。
 *
 * 类型取自 progress_chapters 表：progress 是 double，book / para 是整数，
 * 时间字段可能为 null（尚未完成）。
 */
class ProgressResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     book: int,
     *     para: int,
     *     lang: string,
     *     progress: float,
     *     channel_id: string,
     *     title: string|null,
     *     last_chapter_completed_at: string|null,
     *     completed_at: string|null,
     *     updated_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'book' => (int) $this->book,
            'para' => (int) $this->para,
            'lang' => $this->lang,
            'progress' => (float) $this->progress,
            'channel_id' => $this->channel_id,
            'title' => $this->title,
            'last_chapter_completed_at' => $this->last_chapter_completed_at,
            'completed_at' => $this->completed_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
