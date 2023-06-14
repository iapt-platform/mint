<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;

class NissayaEndingResource extends JsonResource
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
            "ending"=> $this->ending,
            "lang"=> $this->lang,
            "relation"=> $this->relation,
            "case"=> $this->case,
            "count"=> $this->count,
            "editor"=> UserApi::getById($this->editor_id),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
