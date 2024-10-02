<?php
namespace App\Http\Api;
use App\Models\Channel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChannelApi{
    public static function getById($id){
        if(!Str::isUuid($id)){
            return false;
        }
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

    public static function userCanEdit($userUid,$channelUid){
        $channels = ChannelApi::getCanEditByUser($userUid);
        return in_array($channelUid,$channels);
    }
    public static function getCanEditByUser($userUuid=null){
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
                if($value['power'] >= 20){
                   $channelId[$value['res_id']] = $value['res_id'];
                }
            }
        }

        $output = array();
        foreach ($channelId as $key => $value) {
            $output[] = $key;
        }
        return $output;
    }
    public static function canManageByUser($channelId,$userUuid){
        $isOwner = Channel::where('owner_uid', $userUuid)
                    ->where('uid', $channelId)->exists();
        return $isOwner;
    }
    public static function getSysChannel($channel_name,$fallback=""){
        $channel = Channel::where('name',$channel_name)
                    ->where('owner_uid',config("mint.admin.root_uuid"))
                    ->first();
        if(!$channel){
            if(!empty($fallback)){
                $channel = Channel::where('name',$fallback)
                                  ->where('owner_uid',config("mint.admin.root_uuid"))
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

    /**
     * 获取某个studio 的某个语言的自定义书的channel
     * 如果没有，建立
     */
    public static function userBookGetOrCreate($studioId,$lang,$status){
        $channelName = '_user_book_'.$lang;
        $channel = Channel::where('owner_uid',$studioId)
                        ->where('name',$channelName)->first();
        if($channel){
            return $channel->uid;
        }
        $channelUuid = Str::uuid();
        $channel = new Channel;
        $channel->id = app('snowflake')->id();
        $channel->uid = $channelUuid;
        $channel->owner_uid = $studioId;
        $channel->name = $channelName;
        $channel->type = 'original';
        $channel->lang = $lang;
        $channel->editor_id = 0;
        $channel->is_system = true;
        $channel->create_time = time()*1000;
        $channel->modify_time = time()*1000;
        $channel->status = $status;
        $saveOk = $channel->save();
        if($saveOk){
            Log::debug('copy user book : create channel success name='.$channelName);
            return $channel->uid;
        }else{
            Log::error('copy user book : create channel fail.',['channel'=>$channelName,'studioId'=>$studioId]);
            return false;
        }
    }
}
