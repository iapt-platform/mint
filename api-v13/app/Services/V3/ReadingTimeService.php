<?php

namespace App\Services\V3;

use App\Models\ReadingDaily;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 登录用户读经时长的上报与核减。读经时长按策略换积分（见 {@see CreditService::settle()}）。
 *
 * 时长是客户端自报的，服务端只做两道核减：
 * - 当天的上报，计入的秒数不超过「距上次上报过去的时间 + 30 秒余量」——
 *   多台设备同时报、或者一次报个大数，都只按墙钟时间算；
 * - 单日累计不超过 24 小时。
 * 断网补报允许最多 2 天前，补报的那几天不做墙钟核减（换积分时每天另有上限）。
 */
class ReadingTimeService
{
    private const SLACK_SECONDS = 30;

    private const BACKFILL_DAYS = 2;

    /**
     * @throws ValidationException day 早于补报期限或在未来
     */
    public function report(string $userUid, int $seconds, ?string $day): ReadingDaily
    {
        $today = now('UTC')->startOfDay();
        $target = $day ? now('UTC')->parse($day)->startOfDay() : $today;
        if ($target->gt($today) || $target->lt($today->copy()->subDays(self::BACKFILL_DAYS))) {
            throw ValidationException::withMessages(['day' => __('messages.reading_time_day_out_of_range')]);
        }
        $isToday = $target->equalTo($today);

        return DB::transaction(function () use ($userUid, $seconds, $target, $isToday) {
            ReadingDaily::createOrFirst(['user_uid' => $userUid, 'day' => $target->toDateString()]);
            $row = ReadingDaily::where('user_uid', $userUid)->where('day', $target->toDateString())
                ->lockForUpdate()->firstOrFail();

            $allowed = $seconds;
            if ($isToday && $row->last_report_at) {
                $elapsed = (int) $row->last_report_at->diffInSeconds(now(), true);
                $allowed = min($allowed, $elapsed + self::SLACK_SECONDS);
            }
            $allowed = max(0, min($allowed, 86400 - (int) $row->seconds));

            $row->seconds_reported = (int) $row->seconds_reported + $seconds;
            $row->seconds = (int) $row->seconds + $allowed;
            if ($isToday) {
                $row->last_report_at = now();
            }
            $row->save();

            return $row;
        });
    }
}
