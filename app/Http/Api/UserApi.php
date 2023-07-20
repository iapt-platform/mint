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
        $userinfo = new \UserInfo();
        $studio = $userinfo->getName($id);
        return [
            'id'=>$id,
            'nickName'=>$studio['nickname'],
            'userName'=>$studio['username'],
            'realName'=>$studio['username'],
            'avatar'=>'',
        ];
    }
    public static function getByUuid($id){
        $userinfo = new \UserInfo();
        $studio = $userinfo->getName($id);
        return [
            'id'=>$id,
            'nickName'=>$studio['nickname'],
            'userName'=>$studio['username'],
            'avatar'=>'',
        ];
    }
}
