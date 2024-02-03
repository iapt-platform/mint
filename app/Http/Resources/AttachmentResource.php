<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

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
        $filename = $this->bucket.'/'.$this->name;
        $data = [
            "id" => $this->id,
            "user_uid" => $this->user_uid,
            "name" => $filename,
            "filename" => $filename,
            "title" => $this->title,
            "size" => $this->size,
            "content_type" => $this->content_type,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];

        if (App::environment('local')) {
            $data['url'] = Storage::url($filename);
        }else{
            $data['url'] = Storage::temporaryUrl($filename, now()->addDays(6));
        }
        return $data;
    }
}
