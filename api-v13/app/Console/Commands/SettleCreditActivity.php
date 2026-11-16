<?php

namespace App\Console\Commands;

use App\Services\V3\CreditService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * 把登录用户的读经、编辑时长结算成 AI 对话积分。由 ops 每天跑一次（多跑无妨）。
 *
 * 不带参数：从积分上线日（config mint.credits.earn_since）到昨天，补齐所有漏结算的「用户 + 日」——
 * 从没结算过的、或在那天活动结束前就结算了的（见 CreditService::settleMissing）。所以任务漏跑
 * 不管几天，下次一跑就补上。带 --date：只重算那一天的所有用户。
 *
 * 用户查额度 / 提问时已经会顺带结算今天和昨天。可重复执行，结果覆盖、不会重复入账。
 */
#[Signature('credits:settle-activity {--date= : 只重算这一天（UTC，Y-m-d）；不给就补齐上线以来所有漏结算的日子}')]
#[Description('把读经、编辑时长结算成 AI 对话积分（默认补齐上线以来所有漏结算的日子）')]
class SettleCreditActivity extends Command
{
    public function handle(CreditService $credits): int
    {
        if ($this->option('date')) {
            $day = now('UTC')->parse((string) $this->option('date'))->startOfDay();
            $users = $credits->settleDay($day);
            $this->info("{$day->toDateString()}：结算了 {$users} 位用户。");

            return self::SUCCESS;
        }

        $since = now('UTC')->parse((string) config('mint.credits.earn_since'))->startOfDay();
        $until = now('UTC')->subDay()->startOfDay();
        ['pairs' => $pairs, 'users' => $users] = $credits->settleMissing($since, $until);
        $this->info("{$since->toDateString()} ~ {$until->toDateString()}：补结算了 {$users} 位用户的 {$pairs} 个「用户 + 日」。");

        return self::SUCCESS;
    }
}
