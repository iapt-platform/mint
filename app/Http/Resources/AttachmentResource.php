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
            $data['url'] = Storage::temporaryUrl($filename, now()->addDays(2));
        }

        $type = explode('/',$this->content_type);
        if($type[0] === 'image' || $type[0] === 'video') {
            $path_parts = pathinfo($this->name);
            $small = $this->bucket.'/'.$path_parts['filename'] . '_s.jpg';
            $middle = $this->bucket.'/'.$path_parts['filename'] . '_m.jpg';
            if (App::environment('local')) {
                $data['thumbnail'] = [
                    'small'=>Storage::url($small),
                    'middle'=>Storage::url($middle),
                ];
            }else{
                $data['thumbnail'] = [
                    'small' => Storage::temporaryUrl($small, now()->addDays(2)),
                    'middle' => Storage::temporaryUrl($middle, now()->addDays(2)),
                ];
            }
        }
        return $data;
    }
}
