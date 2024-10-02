<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\StudioApi;
use App\Http\Api\ChannelApi;
use App\Models\ArticleCollection;


class CollectionResource extends JsonResource
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
            "studio" => StudioApi::getById($this->owner),
            "childrenNumber" => ArticleCollection::where('collect_id',$this->uid)->count(),
            "status" => $this->status,
            'lang' => $this->lang,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        $channel = ChannelApi::getById($this->default_channel);
        if($channel){
            $data['default_channel'] = $channel;
        }
        if($this->fullArticleList===true){
            $data["article_list"] = \json_decode($this->article_list);
        }else{
            if(isset($this->article_list) && !empty($this->article_list) ){
                $arrList = \json_decode($this->article_list);
                if(is_array($arrList)){
                    $data["article_list"] = array_slice($arrList,0,4);
                }
            }
        }

        return $data;
    }
}
