<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\MdRender;
use App\Http\Api\UserApi;
use App\Http\Api\AuthApi;
use App\Http\Api\ChannelApi;

class SentPrResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //获取用户信息
        $user = AuthApi::current($request);
        $role = 'reader';
        if($user && $user["user_uid"] === $this->editor_uid ){
            $role = 'owner';
        }
        $channel = ChannelApi::getById($this->channel_uid);
        $mode = $request->get("mode",'read');
        return [
            "id"=>$this->id,
            "uid"=>$this->uid,
            "book"=> $this->book_id,
            "paragraph"=> $this->paragraph,
            "word_start"=> $this->word_start,
            "word_end"=> $this->word_end,
            "editor"=> UserApi::getByUuid($this->editor_uid),
            "channel"=> $channel,
            "content"=>$this->content,
            "html"=> MdRender::render($this->content,[$this->channel_uid],null,$mode,$channel['type']),
            "role"=>$role,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
