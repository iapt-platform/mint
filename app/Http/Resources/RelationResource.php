<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Http\Api\ChannelApi;
use App\Models\DhammaTerm;

class RelationResource extends JsonResource
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
            "id"=>$this->id,
            "name"=> $this->name,
            "case"=> $this->case,
            "from"=> json_decode($this->from),
            "to"=> json_decode($this->to),
            "category"=> $this->category,
            "editor"=> UserApi::getByUuid($this->editor_id),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];

        $lang = $request->get('ui-lang');
        //TODO 默认英文
        $uiLang = strtolower($request->get('ui-lang','zh-Hans')) ;
        $term_channel = ChannelApi::getSysChannel("_System_Grammar_Term_{$uiLang}_");
        if($term_channel){
            $data['category_channel'] = $term_channel;
            if(!empty($this->category)){
            $term = DhammaTerm::where("word",$this->category)
                                        ->where('channal',$term_channel)
                                        ->first();
                if($term){
                    $data['category_term']['channelId'] = $term_channel;
                    $data['category_term']['word'] = $term->word;
                    $data['category_term']['id'] = $term->guid;
                    $data['category_term']['meaning'] = $term->meaning;
                }
            }
        }


        return $data;
    }
}
