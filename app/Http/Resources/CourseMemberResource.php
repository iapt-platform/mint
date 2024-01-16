<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;

class CourseMemberResource extends JsonResource
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
            "user_id"=> $this->user_id,
            "course_id"=> $this->course_id,
            "role"=> $this->role,
            "user"=> UserApi::getByUuid($this->user_id),
            "status"=> $this->status,
            'channel_id'=> $this->channel_id,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
        return $data;
    }
}
