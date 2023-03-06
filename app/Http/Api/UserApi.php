<?php
namespace App\Http\Api;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

class UserApi{
    public static function getIdByName($name){
        $userinfo = new \UserInfo();
        return $userinfo->getUserByName($name)['userid'];
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
            'avatar'=>'',
        ];
    }
}
