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
    public static function getCanReadByUser($userUuid=null){
        #获取 user 在某章节 所有有权限的 channel 列表
        $channelId = [];
        //我自己的

        if($userUuid){
            $my = Channel::select('uid')->where('owner_uid', $userUuid)->get();
            foreach ($my as $key => $value) {
                $channelId[$value->uid] = $value->uid;
            }

            //获取共享channel

            $allSharedChannels = ShareApi::getResList($userUuid,2);
            foreach ($allSharedChannels as $key => $value) {
                $channelId[$value['res_id']] = $value['res_id'];
            }
        }
        //获取全网公开的channel
        $my = Channel::select('uid')->where('status', 30)->get();
        foreach ($my as $key => $value) {
            $channelId[$value->uid] = $value->uid;
        }
        $output = array();
        foreach ($channelId as $key => $value) {
            $output[] = $key;
        }
        return $output;
    }
    public static function getSysChannel($channel_name,$fallback=""){
        $channel = Channel::where('name',$channel_name)
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
