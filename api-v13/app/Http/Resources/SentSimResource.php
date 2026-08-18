<?php

namespace App\Http\Resources;

use App\Http\Controllers\CorpusController;
use App\Models\PaliSentence;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $channels = explode(',', $request->input('channels'));
        $mode = $request->input('mode', 'read');
        $sentId = $sent->book.'-'.$sent->paragraph.'-'.$sent->word_begin.'-'.$sent->word_end;
        $Sent = new CorpusController;
        $tpl =
            $data['sent'] = $Sent->getSentTpl($sentId, $channels, $mode);
        $data['sim'] = $this->sim;

        return $data;
    }
}
