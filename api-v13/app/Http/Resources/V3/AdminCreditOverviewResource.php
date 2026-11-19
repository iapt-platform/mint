<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 管理后台 AI 对话积分概览（载荷来自 CreditAdminService::overview()）。
 *
 * recent_days：最近 14 个 UTC 日（今天在前）每天结算出的读经 / 编辑积分与获得积分的人数；
 * 某天之后整片为 0 而用户又在读经，多半是每日结算任务没跑。
 * last_settlement：最近一次结算运行（每日任务或后台手工触发），没有记录为 null。
 */
class AdminCreditOverviewResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     today: string,
     *     earn_since: string,
     *     policy_version: int,
     *     policy: object,
     *     totals: array{user_accounts: int, guest_accounts: int, outstanding_balance: int, earned_total: int, adjusted_total: int, spent_today: int},
     *     recent_days: array<int, array{day: string, users: int, reading: int, editing: int}>,
     *     last_settlement: array{mode: 'day'|'missing', from: string, to: string, users: int, pairs: int|null, trigger: 'cli'|'admin', operator: string|null, ran_at: string, seconds: float}|null
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
