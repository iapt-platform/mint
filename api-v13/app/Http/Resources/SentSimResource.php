<?php

namespace App\Http\Resources;

use App\Http\Controllers\CorpusController;
use App\Models\PaliSentence;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SentSimResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // 获取实际句子信息
        $sent = PaliSentence::find($this->sent2);
        if (! $sent) {
            return [];
        }

        // 过滤掉空值或非 uuid 的 channel，避免空 channels 参数导致 500
        $channels = [];
        foreach (explode(',', (string) $request->input('channels', '')) as $channel) {
            if (Str::isUuid($channel)) {
                $channels[] = $channel;
            }
        }

        $mode = $request->input('mode', 'read');
        $sentId = $sent->book.'-'.$sent->paragraph.'-'.$sent->word_begin.'-'.$sent->word_end;
        $Sent = new CorpusController;
        $data['sent'] = $Sent->getSentTpl($sentId, $channels, $mode);
        $data['sim'] = $this->sim;

        return $data;
    }
}
