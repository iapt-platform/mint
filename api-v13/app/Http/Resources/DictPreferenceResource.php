<?php

namespace App\Http\Resources;

use App\Http\Api\UserApi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DictPreferenceResource extends JsonResource
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
            'id' => strval($this->id),
            'word' => $this->word,
            'count' => $this->count,
            'parent' => $this->parent,
            'note' => $this->note,
            'factors' => $this->factors,
            'confidence' => $this->confidence,
            'updated_at' => $this->updated_at,
            'creator_id' => $this->creator_id,
        ];
        if (! empty($this->editor_id)) {
            $data['editor'] = UserApi::getByUuid($this->editor_id);
        }

        return $data;
    }
}
