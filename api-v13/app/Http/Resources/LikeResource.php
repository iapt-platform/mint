<?php

namespace App\Http\Resources;

use App\Http\Api\UserApi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeResource extends JsonResource
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
            'id' => $this->id,
            'type' => $this->type,
            'target_id' => $this->target_id,
            'target_type' => $this->target_type,
            'context' => $this->context,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
        if ($this->user_id) {
            $data['user'] = UserApi::getByUuid($this->user_id);
        }

        return $data;
    }
}
