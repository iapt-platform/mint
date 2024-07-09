<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\StudioApi;

class ChannelResource extends JsonResource
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
            "uid" => $this->uid,
            "name" => $this->name,
            "summary" => $this->summary,
            "type" => $this->type,
            "studio" => StudioApi::getById($this->owner_uid),
            "lang" => $this->lang,
            "is_system" => $this->is_system,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        if(isset($this->role)){
            $data["role"] = $this->role;
        }
        return $data;
    }
}
