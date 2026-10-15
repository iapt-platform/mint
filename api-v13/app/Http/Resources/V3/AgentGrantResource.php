<?php

namespace App\Http\Resources\V3;

use App\Services\V3\AgentGrantService;
use Illuminate\Http\Request;

/**
 * 一张 agent 写入授权：模型写入时把 token 放进 `X-Agent-Grant` 请求头。
 *
 * 载荷由 {@see AgentGrantService::issue()} 组好，本类只做形状声明。
 */
class AgentGrantResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     token: string,
     *     grantor: string,
     *     ai_model: string,
     *     target_type: string,
     *     target_id: string,
     *     book: int,
     *     expires_at: string
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
