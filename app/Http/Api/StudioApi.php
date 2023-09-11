<?php
namespace App\Http\Api;

require_once __DIR__.'/../../../public/app/ucenter/function.php';
use App\Models\UserInfo;

class StudioApi{
    public static function getIdByName($name){
        /**
         * 获取 uuid
         */
        //TODO 改为studio table
        if(empty($name)){
            return false;
        }
        $userinfo = new \UserInfo();
        $studio = $userinfo->getUserByName($name);
        if($studio){
            return $userinfo->getUserByName($name)['userid'];
        }else{
            return false;
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
        return [
            'id'=>$id,
            'nickName'=>$userInfo['nickname'],
            'realName'=>$userInfo['username'],
            'studioName'=>$userInfo['username'],
            'avatar'=>'',
        ];
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
