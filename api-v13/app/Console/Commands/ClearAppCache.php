<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearAppCache extends Command
{
    /**
     * 名字里不用 cache:clear / cache:forget —— 那两个是 Laravel 内置命令，会撞。
     *
     * @var string
     */
    protected $signature = 'cache:app.clear {name? : 缓存项名称，不传则列出可清理的项} {--all : 清理全部}';

    /**
     * @var string
     */
    protected $description = '清理由应用代码显式写入的缓存（如书目清单），不影响框架自身的缓存';

    /**
     * 可清理的缓存项：名称 => 实际的 cache key 列表。
     *
     * 新增带缓存的接口时在这里登记一行，命令与提示会自动跟上。
     *
     * @var array<string, array{keys: string[], desc: string}>
     */
    private const CACHES = [
        'book-titles' => [
            'keys' => ['book-titles/with-tags'],
            'desc' => '书目清单（含 toc 与 tag），TTL 24 小时',
        ],
    ];

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! $name && ! $this->option('all')) {
            $this->line('可清理的缓存项：');
            foreach (self::CACHES as $key => $item) {
                $cached = collect($item['keys'])->filter(fn ($k) => Cache::has($k))->count();
                $state = $cached > 0 ? "已缓存 {$cached}/".count($item['keys']) : '未缓存';
                $this->line(sprintf('  %-14s %-34s [%s]', $key, $item['desc'], $state));
            }
            $this->newLine();
            $this->line('用法：php artisan cache:app.clear <名称>   或   --all');

            return self::SUCCESS;
        }

        if ($name && ! isset(self::CACHES[$name])) {
            $this->error("未知的缓存项：{$name}");
            $this->line('可选：'.implode(' / ', array_keys(self::CACHES)));

            return self::FAILURE;
        }

        $targets = $name ? [$name => self::CACHES[$name]] : self::CACHES;
        $cleared = 0;
        foreach ($targets as $key => $item) {
            foreach ($item['keys'] as $cacheKey) {
                // forget 对不存在的 key 也返回 true，所以先问一次才能如实报告清了几条
                $existed = Cache::has($cacheKey);
                Cache::forget($cacheKey);
                if ($existed) {
                    $cleared++;
                    $this->info("已清理 {$key}：{$cacheKey}");
                } else {
                    $this->line("跳过 {$key}：{$cacheKey}（本来就没有缓存）");
                }
            }
        }

        $this->newLine();
        $this->info("共清理 {$cleared} 条缓存。下次请求会重新构建。");

        return self::SUCCESS;
    }
}
