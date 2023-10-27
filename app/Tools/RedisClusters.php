<?php
namespace App\Tools;

use Illuminate\Support\Facades\Redis;

class RedisClusters{
    public static function remember($key,$expire,$callback){
        if(Redis::exists($key)){
            return json_decode(Redis::get($key),true);
        }else{
            $valueOrg = $callback();
            if($valueOrg === null){
                $value = null;
            }else{
                $value = json_encode($valueOrg,JSON_UNESCAPED_UNICODE);
            }
            Redis::set($key,$value);
            Redis::expire($key,$expire);
            return $valueOrg;
        }
    }

    public static function put($key,$value,$expire=null){
        $value = json_encode($value,JSON_UNESCAPED_UNICODE);
        Redis::set($key,$value);
        if($expire){
            Redis::expire($key,$expire);
        }
        return $value;
    }

    public static function get($key){
        return json_decode(Redis::get($key),true);
    }

    public static function forget($key){
        return Redis::del($key);
    }

    public static function has($key){
        return Redis::exists($key);
    }
}
