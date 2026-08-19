<?php

namespace App\Console\Commands;

use App\Models\UserInfo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * editor_id 是人类用户的自增 sn，editor_uid 是 uuid。加 editor_uid 这一列之前
 * 写入的术语只有前者，这条命令按 user_infos.id → user_infos.userid 补上。
 *
 * 负数不碰：那是 AI 模型的哨兵值（-1），模型的身份只有 editor_uid，本来就
 * 没有 sn。editor_id = 0 是 admin 本人（user_infos 里就有 id=0 这一行），
 * 照常回填。
 */
#[Signature('terms:backfill-editor-uid {--dry-run : 只统计与回显，不写库}')]
#[Description('把 dhamma_terms 里为空的 editor_uid 按 editor_id 回填成用户 uuid')]
class BackfillTermEditorUid extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $pending = DB::table('dhamma_terms')->whereNull('editor_uid');
        $total = (clone $pending)->count();
        if ($total === 0) {
            $this->info('没有 editor_uid 为空的术语，无需回填。');

            return self::SUCCESS;
        }

        $negative = (clone $pending)->where('editor_id', '<', 0)->count();

        $this->line("editor_uid 为空的术语：{$total} 条");
        if ($negative > 0) {
            $this->line("  · editor_id < 0（AI 模型哨兵）：{$negative} 条 —— 跳过，它们没有 sn");
        }

        $target = (clone $pending)->where('editor_id', '>=', 0);

        $ids = (clone $target)->distinct()->pluck('editor_id');
        if ($ids->isEmpty()) {
            $this->warn('没有可回填的行。');

            return self::SUCCESS;
        }

        /** @var array<int, string> $uidById */
        $uidById = UserInfo::whereIn('id', $ids)->pluck('userid', 'id')->all();

        $missing = $ids->reject(fn ($id) => isset($uidById[$id]))->values();
        if ($missing->isNotEmpty()) {
            $this->warn("有 {$missing->count()} 个 editor_id 在 user_infos 里查不到，这些行会原样保留：");
            foreach ($missing as $id) {
                $count = (clone $target)->where('editor_id', $id)->count();
                $this->warn("  editor_id={$id}  {$count} 条");
            }
        }

        $this->line(str_repeat('-', 60));
        $this->line(sprintf('%-12s %-38s %s', 'editor_id', 'userid', '条数'));

        $plan = [];
        foreach ($ids as $id) {
            if (! isset($uidById[$id])) {
                continue;
            }
            $count = (clone $target)->where('editor_id', $id)->count();
            $plan[$id] = ['uid' => $uidById[$id], 'count' => $count];
            $this->line(sprintf('%-12s %-38s %s', $id, $uidById[$id], $count));
        }

        $willWrite = array_sum(array_column($plan, 'count'));
        $this->line(str_repeat('-', 60));
        $this->line("将回填 {$willWrite} 条，涉及 ".count($plan).' 个用户。');

        if ($dryRun) {
            $this->info('--dry-run：未写库。');

            return self::SUCCESS;
        }

        if (! $this->confirm("确认写入这 {$willWrite} 条？", false)) {
            $this->warn('已取消，未改动任何数据。');

            return self::FAILURE;
        }

        // 一个 editor_id 一条 UPDATE（本例 77 条），比逐行快得多。
        // 走 DB::table 而不是 Eloquent：后者会顺手刷 updated_at，
        // 那是术语的最后修改时间，不该被一次数据补写污染。
        $written = 0;
        $bar = $this->output->createProgressBar(count($plan));
        $bar->start();
        foreach ($plan as $id => $row) {
            $written += DB::table('dhamma_terms')
                ->whereNull('editor_uid')
                ->where('editor_id', $id)
                ->update(['editor_uid' => $row['uid']]);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $left = DB::table('dhamma_terms')->whereNull('editor_uid')->count();
        $this->info("已回填 {$written} 条。");
        $this->line("仍为空的还有 {$left} 条".($left > 0 ? '（AI 哨兵，或查不到的用户）' : '').'。');

        return self::SUCCESS;
    }
}
