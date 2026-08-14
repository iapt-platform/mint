<?php

namespace App\Http\Resources;

use App\Models\Wbw;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscussionCountResource extends JsonResource
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
            'res_id' => $this->res_id,
            'res_type' => $this->res_type,
            'type' => $this->type,
            'editor_uid' => $this->editor_uid,
        ];

        switch ($this->res_type) {
            case 'wbw':
                $wbw = Wbw::where('uid', $this->res_id)
                    ->select(['book_id', 'paragraph', 'wid'])
                    ->first();
                $data['wbw'] = $wbw;
                break;
        }

        return $data;
    }
}
