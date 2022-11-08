<?php
namespace App\Http\Api;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

class StudioApi{
    public static function getIdByName($name){
        $userinfo = new \UserInfo();
        return $userinfo->getUserByName($name)['userid'];
    }
}
