<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLog
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! defined('LARAVEL_START')) {
            return $response;
        }

        $delay = (int) round((microtime(true) - LARAVEL_START) * 1000);

        /**
         * =========================
         * 1. 文件日志（daily）
         * =========================
         */
        Log::channel('daily')->info('api.request', [
            'time' => now()->toTimeString(),
            'delay' => $delay,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        /**
         * =========================
         * 2. 实时监控（Cache / Redis）
         * =========================
         */
        $apiPath = explode('/', trim($request->path(), '/'));

        if (count($apiPath) >= 3 && $apiPath[2] !== 'api') {
            $apiName = $apiPath[2];
            $timeMinute = intdiv(time(), 60);
            $timeSecond = time();

            // 分钟级统计
            $this->updateCache("pref-m/all/{$timeMinute}", $delay);
            $this->updateCache("pref-m/{$apiName}/{$timeMinute}", $delay);

            // 秒级统计（短期）
            $this->updateCache("pref-s/all/{$timeSecond}", $delay, 30);
            $this->updateCache("pref-s/{$apiName}/{$timeSecond}", $delay, 30);
        }

        return $response;
    }

    private function updateCache(string $key, int $delay, int $ttl = 3600): void
    {
        $countKey = "{$key}/count";
        $delayKey = "{$key}/delay";

        // 原子操作（Redis 驱动下安全）
        Cache::increment($countKey);
        Cache::increment($delayKey, $delay);

        // increment 不会刷新 TTL，需手动补
        Cache::put($countKey, Cache::get($countKey, 0), $ttl);
        Cache::put($delayKey, Cache::get($delayKey, 0), $ttl);
    }
}
