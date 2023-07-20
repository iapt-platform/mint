<?php
namespace App\Http\Api;
use App\Models\UserInfo;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

class UserApi{
    public static function getIdByName($name){
        $userinfo = new \UserInfo();
        return $userinfo->getUserByName($name)['userid'];
    }
    public static function getIdByUuid($uuid){
        return UserInfo::where('userid',$uuid)->value('id');
    }
    public static function getIntIdByName($name){
        $userinfo = new \UserInfo();
        return $userinfo->getUserByName($name)['id'];
    }
    public static function getById($id){

        $user = UserInfo::where('id',$id)->first();
        return [
            'id'=>$id,
            'nickName'=>$user['nickname'],
            'userName'=>$user['username'],
            'realName'=>$user['username'],
            'avatar'=>'',
        ];
    }
    public static function getByUuid($id){
        $user = UserInfo::where('userid',$id)->first();
        return [
            'id'=>$id,
            'nickName'=>$user['nickname'],
            'userName'=>$user['username'],
            'realName'=>$user['username'],
            'avatar'=>'',
        ];
    }
}
