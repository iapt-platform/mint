<?php
namespace App\Http\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthApi{
    public static function getToken(Request $request){
        $token = false;
        if($request->hasHeader('Authorization')){
            $token = $request->header('Authorization');
            if(\substr($token,0,6) === 'Bearer'){
                $token = trim(substr($token,6));
                if($token === "null"){
                    return false;
                }
            }
        }
        return $token;
    }
    public static function current(Request $request){
        if($request->hasHeader('Authorization')){
            $token = $request->header('Authorization');
            if(\substr($token,0,6) === 'Bearer'){
                $token = trim(substr($token,6));
                if($token === "null"){
                    return false;
                }
                try{
                    $jwt = JWT::decode($token,new Key(config('app.key'),'HS512'));
                }catch(\Exception $e){
                    return false;
                }
                if($jwt->exp < time()){
                    //过期
                    return false;
                }else{
                    //有效的token
                    return ['user_uid'=>$jwt->uid,'user_id'=>$jwt->id];
                }
            }else{
                return false;
            }
        }else if(isset($_COOKIE['user_uid'])){
            return ['user_uid'=>$_COOKIE['user_uid'],'user_id'=>$_COOKIE['user_id']];
        }else{
            return false;
        }
    }
}
