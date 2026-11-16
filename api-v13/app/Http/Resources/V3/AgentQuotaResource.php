<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 当前账号的 AI 对话额度概况：今日赠送、积分余额、两种提问的价格、今天赚了多少、赚取规则。
 *
 * earn 只对登录用户给出（游客读经不给积分）。
 */
class AgentQuotaResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     account_type: string,
     *     day: string,
     *     resets_at: string,
     *     policy_version: int,
     *     daily_free: int,
     *     free_used: int,
     *     free_remaining: int,
     *     earned_balance: int,
     *     cost: array{normal: int, deep: int},
     *     earned_today: array{reading: int, editing: int},
     *     earn: object
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
