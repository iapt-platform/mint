<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AttachmentResource extends JsonResource
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
            "id" => $this->id,
            "user_uid" => $this->user_uid,
            "name" => $this->bucket.'/'.$this->name,
            "filename" => $this->bucket.'/'.$this->name,
            "title" => $this->title,
            "size" => $this->size,
            "content_type" => $this->content_type,
            "url" => Storage::url($this->bucket.'/'.$this->name),
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        return $data;
    }
}
