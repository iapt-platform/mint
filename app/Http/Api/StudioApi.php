<?php
namespace App\Http\Api;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

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
        $userinfo = new \UserInfo();
        $studio = $userinfo->getName($id);
        if(!$studio){
            return false;
        }
        return [
            'id'=>$id,
            'nickName'=>$studio['nickname'],
            'realName'=>$studio['username'],
            'avatar'=>'',
        ];
    }
}
