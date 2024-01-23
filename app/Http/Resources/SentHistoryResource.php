<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;

class SentHistoryResource extends JsonResource
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
            'id' => $this->id,
            'sent_uid' => $this->sent_uid,
            "editor" => UserApi::getByUuId($this->user_uid),
            'content' => $this->content,
            'landmark' => $this->landmark,
            'pr_from' => $this->pr_from,
            'created_at' => $this->created_at
        ];
        if($this->fork_from){
            $fork_from = ChannelApi::getById($this->fork_from);
            if($fork_from){
                $data['fork_from'] = $fork_from;
                $data['fork_studio'] = StudioApi::getById($fork_from['studio_id']);

            }
        }
        if($this->accepter_uid){
            $data['accepter'] = UserApi::getByUuId($this->accepter_uid);
        }
        return $data;
    }
}
