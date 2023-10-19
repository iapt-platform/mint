<?php
namespace App\Tools;

use Illuminate\Support\Facades\Redis;

class RedisClusters{
    public static function remember($key,$expire,$callback){
        if(Redis::exists($key)){
            return Redis::get($key);
        }else{
            $value = $callback();
            Redis::set($key,$value);
            Redis::expire($key,$expire);
            return $value;
        }
    }

    public static function put($key,$value,$expire=null){
        Redis::set($key,$value);
        if($expire){
            Redis::expire($key,$expire);
        }
        return $value;
    }

    public static function get($key){
        return Redis::get($key);
    }

    public static function forget($key){
        return Redis::del($key);
    }

    public static function has($key){
        return Redis::exists($key);
    }
}
