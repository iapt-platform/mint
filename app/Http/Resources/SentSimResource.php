<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaliSentence;
use App\Models\PaliText;
use App\Http\Api\UserApi;
use App\Http\Api\ChannelApi;
use App\Http\Controllers\CorpusController;
use App\Http\Api\AuthApi;

class SentSimResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //获取实际句子信息
        $sent = PaliSentence::find($this->sent2);
        $channels = explode(',',$request->get('channels'));
        $mode = explode(',',$request->get('mode','read'));
        $sentId = $sent->book.'-'.$sent->paragraph.'-'.$sent->word_begin.'-'.$sent->word_end;
        $Sent = new CorpusController();
        $tpl =
        $data['sent'] = $Sent->getSentTpl($sentId,$channels,$mode);
        $data['sim'] = $this->sim;
        return $data;
    }
}
