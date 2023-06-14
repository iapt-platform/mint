<?php
namespace App\Http\Api;
use App\Models\Channel;

class ChannelApi{
    public static function getById($id){
        $channel = Channel::where("uid",$id)->first();
        if($channel){
            return [
                    'id'=>$id,
                    'name'=>$channel['name'],
                    'type'=>$channel['type'],
                    'lang'=>$channel['lang'],
                    'studio_id'=>$channel['owner_uid'],
                ];
        }else{
            return false;
        }
    }
    public static function getListByUser(){

    }
    public static function getSysChannel($channel_name,$fallback=""){
        $channel=  Channel::where('name',$channel_name)
                    ->where('owner_uid',config("app.admin.root_uuid"))
                    ->first();
        if(!$channel){
            if(!empty($fallback)){
                $channel = Channel::where('name',$fallback)
                                  ->where('owner_uid',config("app.admin.root_uuid"))
                                  ->first();
                if(!$channel){
                    return false;
                }else{
                    return $channel->uid;
                }
            }
            return false;
        }else{
            return $channel->uid;
        }
    }
}
