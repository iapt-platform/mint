<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "title"=> $this->title,
            "subtitle"=> $this->subtitle,
            "teacher"=> UserApi::getById($this->teacher),
            "course_count"=>10,
            "type"=> 1,
            "anthology_id"=> '',
            "start_at"=> $this->start_at,
            "end_at"=> $this->end_at,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "cover"=> $this->cover,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
