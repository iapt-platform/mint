<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Wbw;

class DiscussionCountResource extends JsonResource
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
            'res_id' => $this->res_id,
            'res_type' => $this->res_type,
            'type' => $this->type,
            'editor_uid' => $this->editor_uid
        ];

        switch ($this->res_type) {
            case 'wbw':
                $wbw = Wbw::where('uid',$this->res_id)
                        ->select(['book_id','paragraph','wid'])
                        ->first();
                $data['wbw'] = $wbw;
                break;
        }
        return $data;
    }
}
