<?php

namespace App\Http\Resources;

use App\Http\Api\ChannelApi;
use App\Http\Api\UserApi;
use App\Models\DhammaTerm;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NissayaEndingResource extends JsonResource
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
            'ending' => $this->ending,
            'lang' => $this->lang,
            'relation' => $this->relation,
            'case' => $this->case,
            'from' => json_decode($this->from),
            'count' => $this->count,
            'editor' => UserApi::getByUuid($this->editor_id),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->lang === 'my') {
            $uiLang = strtolower($request->input('ui-lang', 'en'));
            $term_channel = ChannelApi::getSysChannel("_System_Grammar_Term_{$uiLang}_");
            if ($term_channel) {
                $term = DhammaTerm::where('word', $this->ending)
                    ->where('channal', $term_channel)
                    ->first();
                $data['term_channel'] = $term_channel;
                if ($term) {
                    $data['term_id'] = $term->guid;
                }
            }
        }

        return $data;
    }
}
