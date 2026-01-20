<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

use App\Models\UserOperationLog;
use App\Models\UserOperationFrame;
use App\Models\UserOperationDaily;
use App\Http\Api\AuthApi;

class UserOperation
{
    private const MAX_INTERVAL = 600_000; // 10 min (ms)
    private const MIN_INTERVAL = 60_000;  // 1 min (ms)
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = AuthApi::current($request);
        if (! $user) {
            return $response;
        }

        $segments = explode('/', trim($request->path(), '/'));
        if (count($segments) < 3 || $segments[0] !== 'api' || $segments[1] !== 'v2') {
            return $response;
        }

        $method = $request->method();
        $newLog = $this->resolveOperation($segments[2], $method, $request);

        if (! $newLog) {
            return $response;
        }

        $currTime = (int) round(microtime(true) * 1000);

        // 客户端时区（分钟 → 毫秒）
        $clientTimezone = (int) ($request->cookie('timezone', 0)) * -60 * 1000;

        /**
         * =========================
         * 1. 操作日志
         * =========================
         */
        UserOperationLog::forceCreate([
            'id'          => app('snowflake')->id(),
            'user_id'     => $user['user_id'],
            'op_type_id'  => $newLog['op_type_id'],
            'op_type'     => $newLog['op_type'],
            'content'     => $newLog['content'],
            'timezone'    => $clientTimezone,
            'create_time' => $currTime,
        ]);


        /**
         * =========================
         * 2. 活跃时间 Frame
         * =========================
         */
        $lastFrame = UserOperationFrame::where('user_id', $user['user_id'])
            ->latest('updated_at')
            ->first();

        $isNewFrame = true;

        if ($lastFrame) {
            $isNewFrame = ($currTime - (int) $lastFrame->op_end) > self::MAX_INTERVAL;
        }

        if ($isNewFrame) {
            UserOperationFrame::forceCreate([
                'id'        => app('snowflake')->id(),
                'user_id'   => $user['user_id'],
                'op_start'  => $currTime - self::MIN_INTERVAL,
                'op_end'    => $currTime,
                'duration'  => self::MIN_INTERVAL,
                'hit'       => 1,
                'timezone'  => $clientTimezone,
            ]);

            $thisActiveTime = self::MIN_INTERVAL;
        } else {
            $thisActiveTime = $currTime - (int) $lastFrame->op_end;

            $lastFrame->forceFill([
                'op_end'   => $currTime,
                'duration' => $currTime - (int) $lastFrame->op_start,
                'hit'      => $lastFrame->hit + 1,
            ])->save();
        }

        /**
         * =========================
         * 3. Daily 汇总
         * =========================
         */
        $clientTime     = $currTime + $clientTimezone;
        $clientDateMs   = strtotime(gmdate('Y-m-d', $clientTime / 1000)) * 1000;

        $daily = UserOperationDaily::firstOrNew([
            'user_id'  => $user['user_id'],
            'date_int' => $clientDateMs,
        ]);

        if ($daily->exists) {
            $daily->increment('duration', $thisActiveTime);
            $daily->increment('hit');
        } else {
            $id = app('snowflake')->id();
            if (app()->isLocal()) {
                Log::debug('snowflake ' . $id);
            }
            $daily->forceFill([
                'id'       => $id,
                'duration' => self::MIN_INTERVAL,
                'hit'      => 1,
            ])->save();
        }

        return $response;
    }

    /**
     * 根据 API 路径与方法解析操作类型
     */
    private function resolveOperation(string $resource, string $method, Request $request): ?array
    {
        return match ($resource) {
            'channel' => match ($method) {
                'POST' => [
                    'op_type_id' => 11,
                    'op_type'    => 'channel_create',
                    'content'    => $request->input('studio') . '/' . $request->input('name'),
                ],
                'PUT' => [
                    'op_type_id' => 10,
                    'op_type'    => 'channel_update',
                    'content'    => $request->input('name'),
                ],
                default => null,
            },

            'article' => match ($method) {
                'POST' => [
                    'op_type_id' => 21,
                    'op_type'    => 'article_create',
                    'content'    => $request->input('studio') . '/' . $request->input('title'),
                ],
                'PUT' => [
                    'op_type_id' => 20,
                    'op_type'    => 'article_update',
                    'content'    => $request->input('title'),
                ],
                default => null,
            },

            'dict' => [
                'op_type_id' => 30,
                'op_type'    => 'dict_lookup',
                'content'    => $request->input('word'),
            ],

            'terms' => match ($method) {
                'POST' => [
                    'op_type_id' => 42,
                    'op_type'    => 'term_create',
                    'content'    => $request->input('word'),
                ],
                'PUT' => [
                    'op_type_id' => 40,
                    'op_type'    => 'term_update',
                    'content'    => $request->input('word'),
                ],
                default => null,
            },

            'sentence' => match ($method) {
                'POST' => [
                    'op_type_id' => 71,
                    'op_type'    => 'sent_create',
                    'content'    => $request->input('channel'),
                ],
                'PUT' => [
                    'op_type_id' => 70,
                    'op_type'    => 'sent_update',
                    'content'    => $request->input('channel'),
                ],
                default => null,
            },

            'anthology' => match ($method) {
                'POST' => [
                    'op_type_id' => 81,
                    'op_type'    => 'collection_create',
                    'content'    => $request->input('title'),
                ],
                'PUT' => [
                    'op_type_id' => 80,
                    'op_type'    => 'collection_update',
                    'content'    => $request->input('title'),
                ],
                default => null,
            },

            'wbw' => $method === 'POST'
                ? [
                    'op_type_id' => 60,
                    'op_type'    => 'wbw_update',
                    'content'    => implode('_', [
                        $request->input('book'),
                        $request->input('para'),
                        $request->input('channel_id'),
                    ]),
                ]
                : null,

            default => null,
        };
    }
}
