<?php

namespace App\Http\Resources;

use App\Models\ProgressChapter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TocResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'book' => $this->book,
            'paragraph' => $this->paragraph,
            'pali_title' => $this->toc,
            'level' => $this->level,
        ];

        $title = ProgressChapter::where('book', $this->book)
            ->where('para', $this->paragraph)
            ->where('lang', 'zh')
            ->whereNotNull('title')
            ->value('title');
        if (! empty($title)) {
            $data['title'] = $title;
        }
        if ($request->has('channels')) {
            if (strpos($request->input('channels'), ',') === false) {
                $channels = explode('_', $request->input('channels'));
            } else {
                $channels = explode(',', $request->input('channels'));
            }
            $title = ProgressChapter::where('book', $this->book)
                ->where('para', $this->paragraph)
                ->where('channel_id', $channels[0])
                ->whereNotNull('title')
                ->value('title');
            if (! empty($title)) {
                $data['title'] = $title;
            }
            // 查询完成度
            foreach ($channels as $key => $channel) {
                $progress = ProgressChapter::where('book', $this->book)
                    ->where('para', $this->paragraph)
                    ->where('channel_id', $channel)
                    ->value('progress');
                if ($progress) {
                    $data['progress'][] = $progress;
                } else {
                    $data['progress'][] = 0;
                }
            }
        }

        return $data;
    }
}
