<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;
use App\Http\Api\MdRender;
use App\Http\Api\ShareApi;
use App\Http\Api\AuthApi;
use App\Models\UserOperationDaily;
use App\Models\DhammaTerm;

class TermResource extends JsonResource
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
            "guid"=>$this->guid,
            "word"=> $this->word,
            "word_en"=> $this->word_en,
            "meaning"=> $this->meaning,
            "other_meaning"=> $this->other_meaning,
            "tag"=> $this->tag,
            "note"=> $this->note,
            "language"=> $this->language,
            "channal"=> $this->channal,
            "studio" => StudioApi::getById($this->owner),
            "editor"=> UserApi::getById($this->editor_id),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];


        if($request->has('channel') && !empty($request->get('channel'))){
            $channels = explode('_',$request->get('channel')) ;
        }else{
            if(!empty($this->channal) && Str::isUuid($this->channal)){
                $channelId = $this->channal;
                $data["channel"] = ChannelApi::getById($this->channal);
            }else{
                $channelId = ChannelApi::getSysChannel('_community_translation_'.$this->language.'_');
                if(empty($channelId)){
                    $channelId = ChannelApi::getSysChannel('_community_translation_zh-hans_');
                }
            }
            if(!empty($channelId)){
                $channels = [$channelId];
            }else{
                $channels = [];
            }
        }


        if(!empty($this->note)){
            $mdRender = new MdRender(
                [
                    'mode'=>$request->get('mode','read'),
                    'format'=>'react',
                    'studioId'=>$this->owner,
                ]);
            $data["html"]  = $mdRender->convert($this->note,$channels,null);
            $summaryContent = $this->note;
        }else if($request->has('community_summary')){
            $lang = strtolower($this->language);
            if($lang==='zh'){
                $lang='zh-hans';
            }
            $community_channel = ChannelApi::getSysChannel("_community_term_{$lang}_");
            if(empty($community_channel)){
                $community_channel = ChannelApi::getSysChannel('_community_term_zh-hans_');
            }
            if(Str::isUuid($community_channel)){
                            //查找社区解释
                $community_note = DhammaTerm::where("word",$this->word)
                                            ->where('channal',$community_channel)
                                            ->value('note');
                if(!empty($community_note)){
                    $summaryContent = $community_note;
                    $data["summary_is_community"] = true;
                }
            }
        }
        if(isset($summaryContent)){
            $mdRender = new MdRender(
                [
                    'mode'=>$request->get('mode','read'),
                    'format'=>'text',
                    'studioId'=>$this->owner,
                ]);
            $data["summary"]  = $mdRender->convert($summaryContent,$channels,null);
        }

        $user = AuthApi::current($request);
        if(!$user){
            $data["role"] = 'reader';
        }else{
            if($this->owner === $user['user_uid']){
                $data["role"] = 'owner';
            }else if(!empty($this->channal)){
                $power = ShareApi::getResPower($user['user_uid'],$this->channal);
                if($power===20){
                    $data["role"] = 'editor';
                }else if($power===10){
                    $data["role"] = 'reader';
                }else{
                    $data["role"] = 'unknown';
                }
            }
        }
        if($request->has('exp')){
            //毫秒计算的经验值
            $exp = UserOperationDaily::where('user_id',$this->editor_id)
                                                ->where('date_int','<=',date_timestamp_get(date_create($this->updated_at))*1000)
                                                ->sum('duration');
            $data['exp'] = (int)($exp/1000);
        }
        return $data;
    }
}
