<?php

namespace App\Http\Resources;
use App\Http\Api\UserApi;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            "id"=>$this->id,
            "title"=> $this->title,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "parent"=> $this->parent,
            "children_count"=> $this->children_count,
            "editor"=> UserApi::getByUuid($this->editor_uid),
            "res_id"=>$this->res_id,
            "res_type"=> $this->res_type,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
