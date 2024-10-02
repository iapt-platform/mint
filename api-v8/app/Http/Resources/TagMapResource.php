<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TagMap;
use App\Models\Wbw;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;

class TagMapResource extends JsonResource
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
            'table_name' => $this->table_name,
            'anchor_id' => $this->anchor_id,
            'tag_id' => $this->tag_id,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
            "editor"=> UserApi::getByUuid($this->editor_uid),
            "owner" => StudioApi::getById($this->owner_uid),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        switch ($this->table_name) {
            case 'sentence':
                # code...
                break;
            case 'wbw-sentence':
                $data['title'] = '';
                break;
            case 'wbw':
                $wbw = Wbw::where('uid',$this->anchor_id)->first();
                $data['title'] = $wbw->word;
                break;
            default:
                break;
        }
        $tagsId = TagMap::where('anchor_id',$this->anchor_id)->select('tag_id');
        $data['tags'] = $tagsId;
        return $data;
    }
}
