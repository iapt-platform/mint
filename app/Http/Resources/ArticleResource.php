<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\MdRender;

class ArticleResource extends JsonResource
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
            "title" => $this->title,
            "subtitle" => $this->subtitle,
            "summary" => $this->summary,
            "studio"=> \App\Http\Api\StudioApi::getById($this->owner),
            "editor"=> \App\Http\Api\UserApi::getById($this->editor_id),
            "status" => $this->status,
            "lang" => $this->lang,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        if(isset($this->content) && !empty($this->content)){
            $data["content"] = $this->content;
            $data["content_type"] = $this->content_type;
            $data["html"] = MdRender::render($this->content,$request->get('channel'));
        }
        return $data;
    }
}
