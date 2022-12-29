<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Channel;
use App\Models\Article;
use App\Http\Api\StudioApi;

class ShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //获取资源信息
        $res_name="";
        $owner="";
        switch ($this->res_type) {
            case 1:
                # PCS 文档
                break;
            case 2:
                # Channel 版本
                $res = Channel::where('uid',$this->res_id)->first();
                if($res){
                    $res_name = $channel->name;
                    $owner = StudioApi::getById($res->owner_uid);
                }
                break;
            case 3:
                # Article 文章
                $res = Article::where('uid',$this->res_id)->first();
                if($res){
                    $res_name = $res->title;
                    $owner = StudioApi::getById($res->owner);
                }
                break;
            case 4:
                # Collection 文集
                $res = Collection::where('uid',$this->res_id)->first();
                if($res){
                    $res_name = $res->title;
                    $owner = StudioApi::getById($res->owner);
                }
                break;
            case 5:
                # 版本片段 不支持
                break;
            default:
                # code...
                break;
        }
        return [
            "id"=>$this->id,
            "res_id"=> $this->res_id,
            "res_type"=> $this->res_type,
            "power"=> $this->power,
            'res_name'=>$res_name,
            'owner'=>$owner,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
