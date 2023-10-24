<?php
namespace App\Tools;
use App\Http\Api\UserApi;

use Illuminate\Support\Facades\Log;

class OpsLog{
    public static function debug($user_uid,$content){
        try{
            $user = UserApi::getByUuid($user_uid);
            if($user){
                $json = json_encode($content, JSON_UNESCAPED_UNICODE);
                Log::debug("user({$user_uid},{$user['nickName']}, {$user['userName']},) {$json}");
            }else{
                Log::error('no user uuid ,',$user_uid);
            }
        }catch(\Exception $e){
            Log::error($e);
        }
    }
}
