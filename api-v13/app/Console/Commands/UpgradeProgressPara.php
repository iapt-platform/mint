<?php

namespace App\Console\Commands;

use App\Models\PaliSentence;
use App\Models\Progress;
use App\Models\Sentence;
use App\Tools\Tools;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UpgradeProgressPara extends Command
{
    protected $signature = 'upgrade:progress.para {--book=} {--para=} {--channel=} {--fresh : 清除缓存断点，从头开始}';

    protected $description = '更新段落翻译进度（可重入：中断后重跑自动跳过已处理的段落）';

    // 缓存键：记录最后处理到的位置 (book_id, paragraph, channel_uid)，48h 过期
    private const CACHE_KEY = 'upgrade-progress-para:cursor';

    public function handle(): int
    {
        if (Tools::isStop()) {
            return 0;
        }

        if ($this->option('fresh')) {
            Cache::forget(self::CACHE_KEY);
            $this->info('Cleared cached cursor.');
        }

        $this->info('upgrade:progress.para start');
        $startTime = time();

        $book = $this->option('book');
        $para = $this->option('para');
        $channelId = $this->option('channel');

        if ($channelId) {
            $this->line('channel='.$channelId);
        }

        // 构建查询：按 (book_id, paragraph, channel_uid) 分组
        $sentences = $this->buildQuery($book, $para, $channelId);

        // 从缓存恢复断点：跳过上次已处理的记录
        $cursor = Cache::get(self::CACHE_KEY);
        if ($cursor && ! $this->option('book')) {
            $sentences = $this->applyResumeFilter($sentences, $cursor);
            $this->info("Resuming from book={$cursor['book']}, para={$cursor['para']}");
        }

        $total = DB::query()->fromSub($sentences, 't')->count();
        $this->info("sentences: {$total}");

        $curr = 0;

        foreach ($sentences->cursor() as $sentence) {
            // 计算此段落的完成时间和最后更新时间
            $baseQuery = Sentence::where('strlen', '>', 0)
                ->where('book_id', $sentence->book_id)
                ->where('paragraph', $sentence->paragraph)
                ->where('channel_uid', $sentence->channel_uid);

            $finalAt = (clone $baseQuery)->max('created_at');
            $updateAt = (clone $baseQuery)->max('updated_at');

            // 查询段落内每个句子的起始词位置
            $wordStarts = (clone $baseQuery)->pluck('word_start');

            if ($wordStarts->isNotEmpty()) {
                // 累加等效巴利语字符数：每个句子对应的 PaliSentence.length
                $paraStrlen = PaliSentence::where('book', $sentence->book_id)
                    ->where('paragraph', $sentence->paragraph)
                    ->whereIn('word_begin', $wordStarts)
                    ->sum('length');

                $paraInfo = [
                    'book' => $sentence->book_id,
                    'para' => $sentence->paragraph,
                    'channel_id' => $sentence->channel_uid,
                ];

                Progress::updateOrInsert($paraInfo, [
                    'lang' => 'en',
                    'all_strlen' => $paraStrlen,
                    'public_strlen' => $paraStrlen,
                    'created_at' => $finalAt,
                    'updated_at' => $updateAt,
                ]);
            }

            $curr++;

            // 每 500 条保存一次断点到缓存
            if ($curr % 500 === 0) {
                Cache::put(self::CACHE_KEY, [
                    'book' => $sentence->book_id,
                    'para' => $sentence->paragraph,
                ], now()->addHours(48));

                $percent = (int) ($curr * 100 / $total);
                $this->info("[{$percent}%] book={$sentence->book_id} para={$sentence->paragraph}");
                sleep(1);
            }
        }

        // 全部完成，清除断点缓存
        Cache::forget(self::CACHE_KEY);

        $time = time() - $startTime;
        $this->info("upgrade:progress.para finished in {$time}s");

        return 0;
    }

    /** 构建分组查询 */
    private function buildQuery(?string $book, ?string $para, ?string $channelId)
    {
        $table = Sentence::where('strlen', '>', 0);

        if ($book || $para || $channelId) {
            if ($book) {
                $table = $table->where('book_id', $book);
            }
            if ($para) {
                $table = $table->where('paragraph', $para);
            }
            if ($channelId) {
                $table = $table->where('channel_uid', $channelId);
            }
        } else {
            $table = $table->where('book_id', '<', 1000)
                ->whereNotNull('channel_uid');
        }

        return $table->groupBy('book_id', 'paragraph', 'channel_uid')
            ->select('book_id', 'paragraph', 'channel_uid')
            ->orderBy('book_id')
            ->orderBy('paragraph');
    }

    /** 从断点位置之后继续：跳过 (book < X) 或 (book = X and para <= Y) 的记录 */
    private function applyResumeFilter($query, array $cursor)
    {
        return $query->where(function ($q) use ($cursor) {
            $q->where('book_id', '>', $cursor['book'])
                ->orWhere(function ($q2) use ($cursor) {
                    $q2->where('book_id', $cursor['book'])
                        ->where('paragraph', '>', $cursor['para']);
                });
        });
    }
}
