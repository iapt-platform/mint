<?php

namespace App\Tools;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisClusters
{
    public static function remember($key, $expire, $callback)
    {
        if (Redis::exists($key)) {
            return json_decode(Redis::get($key), true);
        } else {
            $valueOrg = $callback();
            if ($valueOrg === null) {
                $value = null;
            } else {
                $value = json_encode($valueOrg, JSON_UNESCAPED_UNICODE);
            }
            Redis::set($key, $value);
            Redis::expire($key, $expire);
            return $valueOrg;
        }
    }

    public static function put($key, $value, $expire = null)
    {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        Redis::set($key, $value);
        if ($expire) {
            Redis::expire($key, $expire);
        }
        return $value;
    }

    public static function get($key)
    {
        return json_decode(Redis::get($key), true);
    }

    public static function forget($key)
    {
        Log::debug('forget start redis key=' . $key . ' has=' . Redis::exists($key));
        $del = Redis::del($key);
        Log::debug('forget end redis key=' . $key . ' has=' . Redis::exists($key) . ' del=' . $del);
        return $del;
    }

    public static function has($key)
    {
        return Redis::exists($key);
    }
}
