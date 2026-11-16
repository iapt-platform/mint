<?php

namespace App\Console\Commands;

use App\Services\V3\AgentAccount;
use App\Services\V3\CreditService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * 给某个登录用户手工加减 AI 对话积分（积分余额池）。--ref 相同的调整只生效一次。
 */
#[Signature('credits:adjust {user : 用户 uuid} {delta : 加减多少积分，可为负} {--reason= : 原因} {--ref= : 调整单号，缺省自动生成}')]
#[Description('手工加减 AI 对话积分')]
class AdjustCredits extends Command
{
    public function handle(CreditService $credits): int
    {
        $user = (string) $this->argument('user');
        if (! Str::isUuid($user)) {
            $this->error('user 必须是用户 uuid');

            return self::FAILURE;
        }
        $delta = filter_var($this->argument('delta'), FILTER_VALIDATE_INT);
        if ($delta === false || $delta === 0) {
            $this->error('delta 必须是非零整数');

            return self::FAILURE;
        }

        $entry = $credits->adjust(
            AgentAccount::user($user),
            $delta,
            (string) ($this->option('ref') ?: Str::uuid()),
            $this->option('reason'),
        );
        $this->info($entry->wasRecentlyCreated
            ? "已调整：{$entry->delta}（ref {$entry->ref}）"
            : "ref {$entry->ref} 已经调整过，未重复入账");

        return self::SUCCESS;
    }
}
