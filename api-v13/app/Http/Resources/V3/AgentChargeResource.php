<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一次提问的扣费结果。剩余额度服务端才算得出来、调用方当场要用，所以随结果返回。
 */
class AgentChargeResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     run_id: string,
     *     cost: int,
     *     charged_free: int,
     *     charged_earned: int,
     *     free_remaining: int,
     *     earned_balance: int,
     *     policy_version: int
     * }
     */
    public function toArray($request): array
    {
        return [
            'run_id' => $this->resource['run_id'],
            'cost' => $this->resource['cost'],
            'charged_free' => $this->resource['charged_free'],
            'charged_earned' => $this->resource['charged_earned'],
            'free_remaining' => $this->resource['free_remaining'],
            'earned_balance' => $this->resource['earned_balance'],
            'policy_version' => $this->resource['policy_version'],
        ];
    }
}
