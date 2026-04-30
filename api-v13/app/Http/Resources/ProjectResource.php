<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ProjectApi;

class ProjectResource extends JsonResource
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
            'id' => $this->uid,
            'title' => $this->title,
            'type' => $this->type,
            'weight' => $this->weight,
            'description' => $this->description,
            'executors_id' => json_decode($this->executors_id),
            'parent_id' => $this->parent_id,
            'parent' => ProjectApi::getById($this->parent_id),
            'path' => ProjectApi::getListByIds(json_decode($this->path)),
            'description' => $this->description,
            "owner" => StudioApi::getById($this->owner_id),
            "editor" => UserApi::getByUuid($this->editor_id),
            'privacy' => $this->privacy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $data;
    }
}
