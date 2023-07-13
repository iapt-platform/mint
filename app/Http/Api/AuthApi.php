<?php
namespace App\Http\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthApi{
    public static function current(Request $request){
        if($request->hasHeader('Authorization')){
            $token = $request->header('Authorization');
            if(\substr($token,0,6) === 'Bearer'){
                $token = trim(substr($token,6));
                if($token === "null"){
                    Log::error('token=null');
                    return false;
                }
                $jwt = JWT::decode($token,new Key(env('APP_KEY'),'HS512'));
                if($jwt->exp < time()){
                    Log::error('Expired');
                    return false;
                }else{
                    //有效的token
                    return ['user_uid'=>$jwt->uid,'user_id'=>$jwt->id];
                }
            }else{
                return false;
            }
        }else if(isset($_COOKIE['user_uid'])){
            Log::error('no token');
            return ['user_uid'=>$_COOKIE['user_uid'],'user_id'=>$_COOKIE['user_id']];
        }else{
            Log::error('no token');
            return false;
        }
    }
}
