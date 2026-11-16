<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 当前生效的 AI 对话积分策略（`config('mint.credits.policy')`，公开）。
 */
class CreditPolicyResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     version: int,
     *     day_timezone: string,
     *     daily_free: array{guest: int, user: int},
     *     cost: array{normal: int, deep: int},
     *     guest_ip_daily_cap: int|null,
     *     earn: array{
     *         reading: array{credits_per_minute: int, daily_cap: int|null},
     *         editing: array{credits_per_minute: int, daily_cap: int|null}
     *     }
     * }
     */
    public function toArray($request): array
    {
        $rules = $this->resource['rules'];

        return [
            'version' => $this->resource['version'],
            'day_timezone' => 'UTC',
            'daily_free' => $rules['daily_free'],
            'cost' => $rules['cost'],
            'guest_ip_daily_cap' => $rules['guest_ip_daily_cap'] ?? null,
            'earn' => $rules['earn'],
        ];
    }
}
