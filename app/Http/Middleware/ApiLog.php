<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ApiLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (defined('LARAVEL_START'))
        {
            $delay = round((microtime(true) - LARAVEL_START)*1000.0);
            $api = [];
            $api[] = date("h:i:sa",LARAVEL_START);
            $api[] = $delay;
            $api[] = $request->method();
            $api[] = $request->path();
            $logFileName = storage_path('logs/api-'.date("Y-m-d").".log");
            $logFile = fopen($logFileName, "a");
            if($logFile){
                fputcsv($logFile, $api);
                fclose($logFile);
            }
            //实时监控
            $apiPath = explode('/',$request->path());
            if(count($apiPath)>=3 && $apiPath[2] !== 'api'){
                $timeMinute = intval(time()/60);
                $timeSecond = time();
                $apiName = $apiPath[2];
                $this->UpdateCache("pref-m/all/{$timeMinute}",$delay);
                $this->UpdateCache("pref-m/{$apiName}/{$timeMinute}",$delay);
                $this->UpdateCache("pref-s/all/{$timeSecond}",$delay,30);
                $this->UpdateCache("pref-s/{$apiName}/{$timeSecond}",$delay,30);
            }
        }
        return $response;
    }

    private function UpdateCache($key,$delay,$expire=3600){
        Redis::set($key."/count",Redis::get($key."/count",0)+1,$expire);
        Redis::expire($key."/count",$expire);
        Redis::set($key."/delay",Redis::get($key."/delay",0)+$delay,$expire);
        Redis::expire($key."/delay",$expire);
    }
}
