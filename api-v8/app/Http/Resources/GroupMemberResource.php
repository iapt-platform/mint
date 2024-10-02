<?php

namespace App\Http\Resources;

use App\Http\Api\UserApi;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMemberResource extends JsonResource
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
            "id"=>$this->id,
            "user_id"=> $this->user_id,
            "group_id"=> $this->group_id,
            "power"=> $this->power,
            "level"=> $this->level,
            "status"=> $this->status,
            "user"=> UserApi::getByUuid($this->user_id),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
