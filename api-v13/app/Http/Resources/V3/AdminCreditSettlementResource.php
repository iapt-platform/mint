<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一次积分结算的结果（载荷来自 CreditAdminService::settle()）。
 *
 * mode=day：只重算了 from 那一天的所有用户，pairs 为 null；
 * mode=missing：补齐了 from ~ to 之间漏结算的「用户 + 日」，pairs 是补了多少个。
 */
class AdminCreditSettlementResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{mode: 'day'|'missing', from: string, to: string, users: int, pairs: int|null, trigger: 'cli'|'admin', operator: string|null, ran_at: string, seconds: float}
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
