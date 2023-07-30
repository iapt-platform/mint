<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaliSentence;
use App\Models\PaliText;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;
use App\Http\Api\ChannelApi;
use App\Http\Controllers\CorpusController;

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
        $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        $sentOrg = [
            "id"=>$sent->id,
            "book"=> $sent->book,
            "para"=> $sent->paragraph,
            "wordStart"=> $sent->word_begin,
            "wordEnd"=> $sent->word_end,
            "editor"=> StudioApi::getById(config("app.admin.root_uuid")),
            "channel"=> ChannelApi::getById($channelId),
            "content"=>$sent->text,
            "html"=> "<span>{$sent->text}</span>",
            "role"=>"member",
            "created_at"=> $sent->created_at,
            "updated_at"=> $sent->updated_at,
        ];
        $resCount = CorpusController::sentResCount($sent->book,$sent->paragraph,$sent->word_begin,$sent->word_end);
        $result = [
            "id" => "{$sent->book}-{$sent->paragraph}-{$sent->word_begin}-{$sent->word_end}",
            "book"=> $sent->book,
            "para"=> $sent->paragraph,
            "wordStart"=> $sent->word_begin,
            "wordEnd"=> $sent->word_end,
            "origin" =>  [$sentOrg],
            "translation" =>  [],
            "layout" =>   "column",
            "tranNum" =>  $resCount['tranNum'],
            "nissayaNum" =>  $resCount['nissayaNum'],
            "commNum" =>  $resCount['commNum'],
            "originNum" =>  $resCount['originNum'],
            "simNum" =>  $resCount['simNum'],
        ];
        $result['path'] = json_decode(PaliText::where('book',$sent->book)
                                        ->where('paragraph',$sent->paragraph)
                                        ->value('path'));
        return $result;
    }
}
