<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('app:upgrade-progress {--fresh : Clear cached progress and start from scratch}')]
#[Description('Run upgrade progress commands (reentrant via Cache)')]
class UpgradeProgress extends Command
{
    // 缓存已完成的步骤名，使命令可重入：中断后重跑会跳过已完成的子命令
    private const CACHE_KEY = 'upgrade-progress:completed-steps';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            Cache::forget(self::CACHE_KEY);
            $this->info('Cleared cached progress.');
        }

        $completedSteps = Cache::get(self::CACHE_KEY, []);

        $steps = [
            'para' => 'upgrade:progress.para',
            'chapter' => 'upgrade:progress.chapter',
        ];

        foreach ($steps as $key => $command) {
            if (in_array($key, $completedSteps)) {
                $this->info("Skipping [{$command}] (already completed).");

                continue;
            }

            $this->info("Running [{$command}]...");
            $exitCode = $this->call($command);

            if ($exitCode !== 0) {
                $this->error("[{$command}] failed with exit code {$exitCode}. Re-run to resume.");

                return $exitCode;
            }

            $completedSteps[] = $key;
            Cache::put(self::CACHE_KEY, $completedSteps, now()->addHours(48));
        }

        Cache::forget(self::CACHE_KEY);
        $this->info('All steps completed.');

        return 0;
    }
}
