<?php
namespace App\Http\Api;

use App\Models\UserInfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class StudioApi{
    public static function getIdByName($name){
        /**
         * 获取 uuid
         */
        //TODO 改为studio table
        if(empty($name)){
            return false;
        }
        $userInfo = UserInfo::where('username',$name)->first();
        if(!$userInfo){
            return false;
        }else{
            return $userInfo->userid;
        }

    }
    public static function getById($id){
        //TODO 改为studio table
        if(empty($id)){
            return false;
        }
        $userInfo = UserInfo::where('userid',$id)->first();
        if(!$userInfo){
            return false;
        }
        $data = [
            'id'=>$id,
            'nickName'=>$userInfo->nickname,
            'realName'=>$userInfo->username,
            'studioName'=>$userInfo->username,
        ];
        if(!empty($userInfo->role)){
            $data['roles'] = json_decode($userInfo->role);
        }
        if($userInfo->avatar){
            $img = str_replace('.jpg','_s.jpg',$userInfo->avatar);
            if (App::environment('local')) {
                $data['avatar'] = Storage::url($img);
            }else{
                $data['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
            }
        }
        return $data;
    }

    public static function getByIntId($id){
        //TODO 改为studio table
        if(empty($id)){
            return false;
        }
        $userInfo = UserInfo::where('id',$id)->first();
        if(!$userInfo){
            return false;
        }
        return [
            'id'=>$userInfo['userid'],
            'nickName'=>$userInfo['nickname'],
            'realName'=>$userInfo['username'],
            'studioName'=>$userInfo['username'],
            'avatar'=>'',
        ];
    }
}
