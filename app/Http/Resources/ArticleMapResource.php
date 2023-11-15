<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Http\Api\MdRender;
use App\Models\Collection;

class ArticleMapResource extends JsonResource
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
            "collect_id" => $this->collect_id,
            "article_id" => $this->article_id,
            "level" => $this->level,
            "title" => $this->title,
            "editor"=> UserApi::getById($this->editor_id),
            "children" => $this->children,
            "deleted_at" => $this->deleted_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        $channels = [];
        if($request->has('channel')){
            $channels = explode('_',$request->get('channel')) ;
        }else{
            $defaultChannel = Collection::where('uid',$this->collect_id)->value('default_channel');
            if($defaultChannel){
                $channels[] = $defaultChannel;
            }
        }
        $mdRender = new MdRender(['format'=>'simple']);
        $data['title_text'] = $mdRender->convert($this->title,$channels);
        if ($request->get('view') === 'article') {
            $collection = Collection::where('uid',$this->collect_id)->first();
            if($collection){
                $data['collection']['id']=$collection->uid;
                $data['collection']['title']=$collection->title;
            }
        }
        return $data;
    }
}
