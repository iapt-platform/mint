<?php

namespace App\Http\Resources;

use App\Http\Api\StudioApi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
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
            'uid' => $this->uid,
            'name' => $this->name,
            'summary' => $this->summary,
            'type' => $this->type,
            'studio' => StudioApi::getById($this->owner_uid),
            'lang' => $this->lang,
            'is_system' => $this->is_system,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
        ];
        if (isset($this->source_type)) {
            $data['source_type'] = $this->source_type;
        }
        if (isset($this->source_id)) {
            $data['source_id'] = $this->source_id;
        }
        if (isset($this->progress)) {
            $data['progress'] = $this->progress;
        }
        if (isset($this->role)) {
            $data['role'] = $this->role;
        }

        return $data;
    }
}
