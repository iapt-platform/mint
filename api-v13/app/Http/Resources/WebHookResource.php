<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebHookResource extends JsonResource
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
            'res_type' => $this->res_type,
            'res_id' => $this->res_id,
            'url' => $this->url,
            'receiver' => $this->receiver,
            'event' => json_decode($this->event),
            'status' => $this->status,
            'fail' => $this->fail,
            'success' => $this->success,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $data;
    }
}
