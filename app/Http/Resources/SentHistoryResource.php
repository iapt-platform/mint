<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;

class SentHistoryResource extends JsonResource
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
            'sent_uid' => $this->sent_uid,
            "editor" => UserApi::getByUuId($this->user_uid),
            'content' => $this->content,
            'landmark' => $this->landmark,
            'created_at' => $this->created_at
        ];
        return $data;
    }
}
