<?php

namespace App\Http\Resources;

use App\Http\Api\ProjectApi;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
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
            'owner' => StudioApi::getById($this->owner_id),
            'editor' => UserApi::getByUuid($this->editor_id),
            'privacy' => $this->privacy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $data;
    }
}
