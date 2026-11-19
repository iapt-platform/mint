<?php

namespace App\Console\Commands;

use App\Services\V3\CreditAdminService;
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
    public function handle(CreditAdminService $credits): int
    {
        // 与管理后台「手工结算」同一入口，最近一次运行记录也一起写（后台概览显示）
        $day = $this->option('date') ? now('UTC')->parse((string) $this->option('date')) : null;
        $result = $credits->settle($day);

        $this->info($result['mode'] === 'day'
            ? "{$result['from']}：结算了 {$result['users']} 位用户。"
            : "{$result['from']} ~ {$result['to']}：补结算了 {$result['users']} 位用户的 {$result['pairs']} 个「用户 + 日」。");

        return self::SUCCESS;
    }
}
