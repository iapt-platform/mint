<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Resources\HeartbeatV3Resource;

class HeartbeatV3Controller extends Controller
{
    /**
     * 心跳
     *
     * 取代 `GET /v2/heartbeat`（`HeartbeatController@index`）。v2 那份在
     * dashboard-v4 下线前原样保留，不要改动。
     *
     * **这不是健康检查。** 它不查数据库、不查 OpenSearch / RabbitMQ / Cache，
     * 只回答一件事：进程还在服务吗、是不是被运维停机了。供前端与负载均衡做
     * 存活探测（liveness）用，开销接近零，可以高频调用。
     *
     * 真正的健康检查是 `GET /v2/health-check`（会逐项探测外部依赖并返回 checks
     * 明细），将来单独迁为 `/v3/health-check`，与本端点是两件事，不要混用。
     *
     * 仓库根目录存在 `.stop` 文件时返回 503（运维停机开关），此时响应体是
     * RFC 9457 Problem Details 而非正常结构。
     *
     * @unauthenticated
     *
     * @responseStatus 503 服务已进入停机维护状态
     */
    public function show(): HeartbeatV3Resource
    {
        if (file_exists(base_path('.stop'))) {
            // 用 BusinessException 而不是 abort(503)：兜底处理器会屏蔽 5xx 的 detail
            // （防止泄露内部错误），而停机是预期状态，维护文案必须原样送到客户端
            throw new BusinessException(__('site.maintenance'), 503, 'maintenance');
        }

        return HeartbeatV3Resource::make([
            'status' => 'ok',
            'checked_at' => now(),
        ]);
    }
}
