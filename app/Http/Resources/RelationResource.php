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
            "match"=> json_decode($this->match),
            "category"=> $this->category,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];


        if(!$request->has('vocabulary')){
            //TODO 默认英文
            $data["editor"] = UserApi::getByUuid($this->editor_id);
            $lang = $request->get('ui-lang');

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
                $data['name_channel'] = $term_channel;
                $term_name = DhammaTerm::where("word",$this->name)
                    ->where('channal',$term_channel)
                    ->first();
                if($term_name){
                    $data['name_term']['channelId'] = $term_channel;
                    $data['name_term']['word'] = $term_name->word;
                    $data['name_term']['id'] = $term_name->guid;
                    $data['name_term']['meaning'] = $term_name->meaning;
                }
            }
        }
        /*

*/

        return $data;
    }
}
