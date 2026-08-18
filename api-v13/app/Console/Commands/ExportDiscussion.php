<?php

namespace App\Console\Commands;

use App\Http\Api\ChannelApi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExportDiscussion extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:discussion
     */
    protected $signature = 'export:discussion {editor : The editor UID to export discussions for}';

    /**
     * The console command description.
     */
    protected $description = 'Export discussions made by a specific editor to a Markdown file';

    /** @var string 巴利原文 channel_uid */
    private string $orgChannelId;

    /** @var resource 输出文件句柄（流式写入，避免大字符串堆积在内存） */
    private $fileHandle;

    /** @var string 输出文件路径 */
    private string $outputPath;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $editorUid = $this->argument('editor');

        $this->info("Fetching discussions for editor: {$editorUid}");

        // 1. 获取巴利原文 channel_uid
        $this->orgChannelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        if (! $this->orgChannelId) {
            $this->error('Failed to retrieve Pali source channel ID.');

            return self::FAILURE;
        }
        $this->info("Pali channel ID: {$this->orgChannelId}");

        // 2. 统计总数（用于进度条）
        $total = DB::table('discussions')
            ->where('editor_uid', $editorUid)
            ->where('status', 'active')
            ->count();

        if ($total === 0) {
            $this->warn("No discussions found for editor: {$editorUid}");

            return self::SUCCESS;
        }

        $this->info("Found {$total} discussion(s). Processing...");

        // 3. 打开文件句柄（流式写入，不在内存中拼接整个 Markdown）
        $filename = "discussion_export_{$editorUid}_".now()->format('YmdHis').'.md';
        $this->outputPath = storage_path("app/tmp/{$filename}");
        $this->fileHandle = fopen($this->outputPath, 'w');
        if (! $this->fileHandle) {
            $this->error("Cannot open file for writing: {$this->outputPath}");

            return self::FAILURE;
        }

        // 写文件头
        $this->writeLine("# 讨论导出报告\n");
        $this->writeLine("- **Editor UID**: {$editorUid}");
        $this->writeLine('- **导出时间**: '.now()->toDateTimeString());
        $this->writeLine("\n---\n");

        // 4. 分批处理（每批 50 条），避免内存溢出
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        DB::table('discussions')
            ->where('editor_uid', $editorUid)
            ->where('status', 'active')
            ->orderBy('created_at', 'asc')
            ->select(['id', 'res_id', 'res_type', 'content', 'created_at'])
            ->chunk(50, function ($discussions) use ($progressBar) {
                $this->processChunk($discussions);
                $progressBar->advance($discussions->count());

                // 每批处理完后主动释放内存
                gc_collect_cycles();
            });

        $progressBar->finish();
        $this->newLine();

        fclose($this->fileHandle);

        $this->info("\n✅ 导出完成！文件已保存到: {$this->outputPath}");

        return self::SUCCESS;
    }

    /**
     * 处理一批 discussions。
     */
    private function processChunk(Collection $discussions): void
    {
        // --- 批量查译文 sentences ---
        $resIds = $discussions->pluck('res_id')->unique()->values()->all();

        $translationMap = DB::table('sentences')
            ->whereIn('uid', $resIds)
            ->select([
                'uid',
                'book_id',
                'paragraph',
                'word_start',
                'word_end',
                'content',
                'channel_uid',
            ])
            ->get()
            ->keyBy('uid');

        // --- 批量查 sent_histories（分小批，避免超大 IN） ---
        $historiesMap = [];
        foreach (array_chunk($resIds, 100) as $batch) {
            DB::table('sent_histories')
                ->whereIn('sent_uid', $batch)
                ->orderBy('create_time', 'asc')
                ->select(['sent_uid', 'content', 'create_time'])
                ->each(function ($row) use (&$historiesMap) {
                    $historiesMap[$row->sent_uid][] = $row;
                });
        }

        // --- 收集本批所有唯一坐标，批量查巴利原文 ---
        $coordKeys = [];
        foreach ($translationMap as $t) {
            $key = "{$t->book_id}_{$t->paragraph}_{$t->word_start}_{$t->word_end}";
            $coordKeys[$key] = $t;
        }
        $paliMap = $this->fetchPaliSentences($coordKeys);

        // --- 写 Markdown ---
        foreach ($discussions as $discussion) {
            $sentUid = $discussion->res_id;
            $translation = $translationMap->get($sentUid);
            if (! $translation) {
                continue;
            }

            $coordKey = "{$translation->book_id}_{$translation->paragraph}_{$translation->word_start}_{$translation->word_end}";
            $pali = $paliMap[$coordKey] ?? null;
            $paliContent = $pali ? trim($pali->content ?? '（无原文）') : '（未找到巴利原文）';

            $discussionCreatedAt = $discussion->created_at
                ? Carbon::parse($discussion->created_at)
                : null;

            $histories = $historiesMap[$sentUid] ?? [];
            $matchedHistory = $this->findClosestHistory($histories, $discussionCreatedAt);
            $translationAtTime = $matchedHistory
                ? trim($matchedHistory->content)
                : trim($translation->content ?? '（无译文内容）');

            $this->writeLine("# {$paliContent}\n");
            $this->writeLine("  - **历史译文**: {$translationAtTime}");
            $this->writeLine('  - **评论**: '.trim($discussion->title ?? '').trim($discussion->content ?? ''));
            $this->writeLine("  - **当前译文**: {$translation->content}");
            $this->writeLine('');
        }

        // 显式释放本批数据
        unset($translationMap, $historiesMap, $coordKeys, $paliMap);
    }

    /**
     * 批量查询巴利原文，每组最多 30 个坐标，避免超大 SQL。
     *
     * @param  array<string, object>  $coordKeys  key="{book_id}_{paragraph}_{word_start}_{word_end}"
     * @return array<string, object>
     */
    private function fetchPaliSentences(array $coordKeys): array
    {
        $paliMap = [];

        foreach (array_chunk(array_values($coordKeys), 30) as $group) {
            $results = DB::table('sentences')
                ->where('channel_uid', $this->orgChannelId)
                ->where(function ($q) use ($group) {
                    foreach ($group as $t) {
                        $q->orWhere(function ($sub) use ($t) {
                            $sub->where('book_id', $t->book_id)
                                ->where('paragraph', $t->paragraph)
                                ->where('word_start', $t->word_start)
                                ->where('word_end', $t->word_end);
                        });
                    }
                })
                ->select(['book_id', 'paragraph', 'word_start', 'word_end', 'content'])
                ->get();

            foreach ($results as $ps) {
                $key = "{$ps->book_id}_{$ps->paragraph}_{$ps->word_start}_{$ps->word_end}";
                $paliMap[$key] = $ps;
            }

            unset($results);
        }

        return $paliMap;
    }

    /**
     * 流式写入一行到文件。
     */
    private function writeLine(string $line): void
    {
        fwrite($this->fileHandle, $line."\n");
    }

    /**
     * 在历史记录中找评论发布时间之前最近的那条。
     * 若全部在评论之后，则退而取最早一条。
     *
     * @param  array  $histories  sent_histories（已按 create_time ASC 排序）
     * @param  Carbon|null  $discussionCreatedAt  评论发布时间
     */
    private function findClosestHistory(array $histories, ?Carbon $discussionCreatedAt): ?object
    {
        if (empty($histories)) {
            return null;
        }

        if (! $discussionCreatedAt) {
            return end($histories) ?: null;
        }

        $discussionTimestamp = $discussionCreatedAt->timestamp;
        $best = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($histories as $h) {
            $historyTime = (int) $h->create_time;
            if ($historyTime <= $discussionTimestamp) {
                $diff = $discussionTimestamp - $historyTime;
                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $best = $h;
                }
            }
        }

        // 所有历史都在评论之后 → 取最早一条
        return $best ?? $histories[0];
    }
}
