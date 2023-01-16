<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use App\Http\Api\MdRender;
use Illuminate\Http\Resources\Json\JsonResource;

class SentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
                "content"=>$this->content,
                "html"=> MdRender::render($this->content,$this->channel_uid),
                "book"=> $this->book_id,
                "paragraph"=> $this->paragraph,
                "word_start"=> $this->word_start,
                "word_end"=> $this->word_end,
                "editor"=> \App\Http\Api\StudioApi::getById($this->editor_uid),
                "channel"=> [
                    "name"=>"channel",
	                "id"=> $this->channel_uid,
                ],
                "updated_at"=> $this->updated_at,
            ];
    }
}
