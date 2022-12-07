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
                    'studio_id'=>$channel['owner_uid'],
                ];
        }else{
            return false;
        }
    }
    public static function getListByUser(){

    }
}
