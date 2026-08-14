<?php

namespace App\Http\Resources;

use App\Http\Api\GroupApi;
use App\Http\Api\UserApi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $roles = ['owner', 'manager', 'member'];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'group_id' => $this->group_id,
            'group' => GroupApi::getById($this->group_id),
            'power' => $this->power,
            'role' => $roles[$this->power],
            'level' => $this->level,
            'status' => $this->status,
            'user' => UserApi::getByUuid($this->user_id),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
