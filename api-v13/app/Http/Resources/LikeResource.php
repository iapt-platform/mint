<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;

class LikeResource extends JsonResource
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
            'type' => $this->type,
            'target_id' => $this->target_id,
            'target_type' => $this->target_type,
            'context' => $this->context,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
        if($this->user_id){
            $data['user'] = UserApi::getByUuid($this->user_id);
        }
        return $data;
    }
}
