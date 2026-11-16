<?php

namespace App\Services\V3;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * 管理后台统计的时间范围：UTC 日的闭区间 [from, to]。缺省近 30 天（到今天），最长 366 天。
 */
class StatsRange
{
    public const DEFAULT_DAYS = 30;

    public const MAX_DAYS = 366;

    /**
     * @param  array{from?: string, to?: string}  $input
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     *
     * @throws ValidationException 区间超过 366 天
     */
    public static function resolve(array $input): array
    {
        $to = isset($input['to']) ? CarbonImmutable::parse($input['to'], 'UTC')->startOfDay() : CarbonImmutable::now('UTC')->startOfDay();
        $from = isset($input['from']) ? CarbonImmutable::parse($input['from'], 'UTC')->startOfDay() : $to->subDays(self::DEFAULT_DAYS - 1);

        if ($from->gt($to) || $from->diffInDays($to) + 1 > self::MAX_DAYS) {
            throw ValidationException::withMessages(['from' => __('messages.stats_range_too_long', ['days' => self::MAX_DAYS])]);
        }

        return [$from, $to];
    }
}
