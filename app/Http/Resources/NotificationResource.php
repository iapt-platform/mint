<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;

class NotificationResource extends JsonResource
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
            "from"=> UserApi::getByUuid($this->from),
            "to"=> UserApi::getByUuid($this->to),
            "url"=> $this->url,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "res_type"=> $this->res_type,
            "res_id"=> $this->res_id,
            "status"=> $this->status,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
        return $data;
    }
}
