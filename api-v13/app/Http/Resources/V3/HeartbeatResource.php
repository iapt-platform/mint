<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

class HeartbeatResource extends BaseResource
{
    /**
     * 心跳结果。
     *
     * status 目前只有 'ok' 一个取值——被停机时走 503 的 Problem Details 分支，
     * 不会到这里。注意这里不含任何依赖项的健康状态，那是 health-check 的职责。
     *
     * @param  Request  $request
     * @return array{
     *     status: string,
     *     checked_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'status' => $this->resource['status'],
            'checked_at' => $this->resource['checked_at'],
        ];
    }
}
