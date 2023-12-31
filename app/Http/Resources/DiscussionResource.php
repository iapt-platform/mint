<?php

namespace App\Http\Resources;
use App\Http\Api\UserApi;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Discussion;
use App\Models\Sentence;
use Illuminate\Support\Str;
use App\Http\Api\MdRender;

class DiscussionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $data = [
            "id"=>$this->id,
            "title"=> $this->title,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "parent"=> $this->parent,
            "status"=> $this->status,
            "editor"=> UserApi::getByUuid($this->editor_uid),
            "res_id"=>$this->res_id,
            "res_type"=> $this->res_type,
            "type"=> $this->type,
            "tpl_id"=> $this->tpl_id,
            'children_count' => Discussion::where('parent',$this->id)->count(),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
        $channels = array();
        switch ($this->res_type) {
            case 'sentence':
                $channelId = Sentence::where('uid',$this->res_id)->value('channel_uid');
                if(Str::isUuid($channelId)){
                    $channels[] = $channelId;
                }
                break;
            default:
                # code...
                break;
        }
        if(count($channels)>0){
            $data["html"] = MdRender::render($this->content,$channels,null,'read');
            $data["summary"] = MdRender::render($this->content,$channels,null,'read','translation','markdown','text');
        }

        return $data;
    }
}
