<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Models\UserOperationDaily;
use App\Models\DictInfo;
use App\Http\Api\MdRender;

class UserDictResource extends JsonResource
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
		 'id'=>$this->id,
         'word'=>$this->word,
         'type'=>$this->type,
         'grammar'=>$this->grammar,
         'mean'=>$this->mean,
         'parent'=>$this->parent,
         'note'=>$this->note,
         'factors'=>$this->factors,
         'source'=>$this->source,
         'status'=>$this->status,
         'confidence'=>$this->confidence,
         'updated_at'=>$this->updated_at,
         'creator_id'=>$this->creator_id,
        ];
        if(!empty($this->note)){
            $mdRender = new MdRender(['format'=>'react','lang'=>'zh-Hans']);
            $data['note'] = $mdRender->convert($this->note);
        }
        if($request->get('view')==='community'){
            $data['editor'] = UserApi::getById($this->creator_id);
            //毫秒计算的经验值
            $exp = UserOperationDaily::where('user_id',$this->creator_id)
                                                ->where('date_int','<=',date_timestamp_get(date_create($this->updated_at))*1000)
                                                ->sum('duration');
            $data['exp'] = (int)($exp/1000);
        }
        if($request->get('view')==='all'){
            $data['dict'] = DictInfo::where('id',$this->dict_id)->select(['id','name','shortname'])->first();
        }
        return $data;
    }
}
