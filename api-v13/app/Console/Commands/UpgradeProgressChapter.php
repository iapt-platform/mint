<?php

namespace App\Console\Commands;

use App\Http\Api\MdRender;
use App\Models\Channel;
use App\Models\PaliSentence;
use App\Models\PaliText;
use App\Models\Progress;
use App\Models\ProgressChapter;
use App\Models\Sentence;
use App\Models\TagMap;
use App\Services\PaliTextService;
use App\Tools\Markdown;
use App\Tools\Tools;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class UpgradeProgressChapter extends Command
{
    protected $signature = 'upgrade:progress.chapter {--book=} {--para=} {--channel=} {--driver=str} {--fresh : 清除缓存断点，从头开始}';

    protected $description = '更新章节完成度（可重入：中断后重跑自动跳过已处理的 book）';

    const COMPLETION_RATE = 0.9;

    // 缓存键：记录最后处理完成的 book_id，48h 过期
    private const CACHE_KEY = 'upgrade-progress-chapter:cursor';

    public function handle(): int
    {
        if (Tools::isStop()) {
            return 0;
        }

        if ($this->option('fresh')) {
            Cache::forget(self::CACHE_KEY);
            $this->info('Cleared cached cursor.');
        }

        $paliTextService = app(PaliTextService::class);

        $this->info('upgrade:progress.chapter start.');
        $startTime = time();

        $book = $this->option('book');
        $para = $this->option('para');
        $channelId = $this->option('channel');

        if ($channelId) {
            $this->line('channel='.$channelId);
        }

        Markdown::driver($this->option('driver'));

        $tagCount = 0;

        // 第一步：查询有译文的 book 列表
        $books = $this->buildBookList($book);

        // 从缓存恢复断点：跳过上次已完成的 book
        $lastBookId = Cache::get(self::CACHE_KEY);
        if ($lastBookId && ! $book) {
            $books = $books->filter(fn ($b) => $b->book_id > $lastBookId)->values();
            $this->info("Resuming after book={$lastBookId}");
        }

        $totalBook = $books->count();

        foreach ($books as $bookIdx => $bookRow) {
            $this->info('['.($bookIdx + 1)."/{$totalBook}] book={$bookRow->book_id}");

            $chapters = $this->getChapters($bookRow->book_id, $para);

            foreach ($chapters as $chapter) {
                // 计算章节对应的巴利语总字符数
                $chapterEnd = $chapter->paragraph + $chapter->chapter_len - 1;
                $chapterStrlen = PaliSentence::where('book', $bookRow->book_id)
                    ->whereBetween('paragraph', [$chapter->paragraph, $chapterEnd])
                    ->sum('length');

                if ($chapterStrlen == 0) {
                    $this->error("chapter_strlen=0 book:{$bookRow->book_id} para:{$chapter->paragraph}-{$chapterEnd}");

                    continue;
                }

                // 按 channel 分组统计已翻译字符数
                $progressQuery = Progress::where('book', $bookRow->book_id)
                    ->whereBetween('para', [$chapter->paragraph, $chapterEnd]);

                if ($channelId) {
                    $progressQuery->where('channel_id', $channelId);
                }

                $channelProgress = $progressQuery->groupBy('channel_id')
                    ->selectRaw('channel_id, sum(all_strlen) as cp_len')
                    ->get();

                foreach ($channelProgress as $final) {
                    $tagCount += $this->processChapterChannel(
                        $bookRow->book_id,
                        $chapter,
                        $chapterEnd,
                        $chapterStrlen,
                        $final,
                        $paliTextService,
                    );
                }
            }

            // 每完成一本书，保存断点
            Cache::put(self::CACHE_KEY, $bookRow->book_id, now()->addHours(48));
        }

        // 全部完成，清除断点缓存
        Cache::forget(self::CACHE_KEY);

        $time = time() - $startTime;
        $this->info("upgrade:progress.chapter finished in {$time}s tag count:{$tagCount}");

        return 0;
    }

    /** 查询有译文的 book 列表，按 book_id 排序 */
    private function buildBookList(?string $book)
    {
        if ($book) {
            $table = Sentence::where('book_id', $book);
        } else {
            $table = Sentence::where('strlen', '>', 0)
                ->where('book_id', '<', 1000)
                ->whereNotNull('channel_uid');
        }

        return $table->groupBy('book_id')
            ->select('book_id')
            ->orderBy('book_id')
            ->get();
    }

    /** 获取某本书的章节列表（level 1-7） */
    private function getChapters(int $bookId, ?string $para)
    {
        $table = PaliText::where('book', $bookId);

        if ($para) {
            $table = $table->where('paragraph', '<=', $para);
        }

        return $table->where('level', '>', 0)
            ->where('level', '<', 8)
            ->select('paragraph', 'chapter_strlen', 'chapter_len')
            ->get();
    }

    /** 处理单个章节×channel 的进度更新，返回新增 tag 数 */
    private function processChapterChannel(
        int $bookId,
        $chapter,
        int $chapterEnd,
        int $chapterStrlen,
        $final,
        PaliTextService $paliTextService,
    ): int {
        $tagCount = 0;

        // 查询该 channel 在此章节范围内的完成时间
        $baseProgress = Progress::where('book', $bookId)
            ->whereBetween('para', [$chapter->paragraph, $chapterEnd])
            ->where('channel_id', $final->channel_id);

        $finalAt = (clone $baseProgress)->max('created_at');
        $updateAt = (clone $baseProgress)->max('updated_at');

        // 获取译文内容，用于生成摘要
        $transTexts = Sentence::where('book_id', $bookId)
            ->whereBetween('paragraph', [$chapter->paragraph + 1, $chapterEnd])
            ->where('channel_uid', $final->channel_id)
            ->select('content')
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();

        $mdRender = new MdRender(['format' => 'simple']);

        // 章节标题
        $title = Sentence::where('book_id', $bookId)
            ->where('paragraph', $chapter->paragraph)
            ->where('channel_uid', $final->channel_id)
            ->value('content');
        $title = $mdRender->convert($title, [$final->channel_id]);

        // 拼接摘要，最多 255 字符
        $summaryText = '';
        foreach ($transTexts as $text) {
            $textContent = $mdRender->convert($text->content, [$final->channel_id]);
            $summaryText .= str_replace("\n", '', $textContent);
            if (mb_strlen($summaryText, 'UTF-8') > 255) {
                break;
            }
        }

        // 查询 channel 语言
        $channelLang = Channel::where('uid', $final->channel_id)->value('lang');
        $lang = explode('-', $channelLang)[0];

        $attributes = [
            'book' => $bookId,
            'para' => $chapter->paragraph,
            'channel_id' => $final->channel_id,
        ];

        $validator = Validator::make($attributes, [
            'book' => 'integer',
            'para' => 'integer',
            'channel_id' => 'uuid',
        ]);

        if ($validator->fails()) {
            $this->error('Validator failed: '.json_encode($attributes));

            return 0;
        }

        // firstOrNew：存在则更新，不存在则新建
        $chapterData = ProgressChapter::firstOrNew($attributes);

        $progress = $final->cp_len / $chapterStrlen;
        $addChapter = false;

        // 进度 >= 90% 视为完成
        if ($progress >= self::COMPLETION_RATE && empty($chapterData->completed_at)) {
            $chapterData->completed_at = $finalAt;
            $addChapter = true;
        }

        $chapterData->lang = $lang;
        $chapterData->all_trans = $progress;
        $chapterData->public = $progress;
        $chapterData->progress = $progress;
        $chapterData->title = $title ? mb_substr($title, 0, 255, 'UTF-8') : '';
        $chapterData->summary = $summaryText ? mb_substr($summaryText, 0, 255, 'UTF-8') : '';
        $chapterData->created_at = $finalAt;
        $chapterData->updated_at = $updateAt;
        $chapterData->save();

        // 新完成的章节：向上更新父级目录的 last_chapter_completed_at
        if ($addChapter) {
            $this->updateParentChapters($bookId, $chapter->paragraph, $final->channel_id, $finalAt, $paliTextService);
        }

        // 更新标签映射
        $tagCount += $this->syncTags($bookId, $chapter->paragraph, $chapterData->uid);

        return $tagCount;
    }

    /** 向上遍历父章节，更新 last_chapter_completed_at 和 completed_chapters 计数 */
    private function updateParentChapters(int $bookId, int $para, string $channelId, $finalAt, PaliTextService $paliTextService): void
    {
        $currPara = $para;

        while ($parent = $paliTextService->getParent($bookId, $currPara)) {
            $parentChapter = ProgressChapter::where('book', $bookId)
                ->where('para', $parent->paragraph)
                ->where('channel_id', $channelId)
                ->first();

            if (! $parentChapter) {
                break;
            }

            $currPara = $parent->paragraph;

            if (
                is_null($parentChapter->last_chapter_completed_at) ||
                Carbon::parse($finalAt)->gt(Carbon::parse($parentChapter->last_chapter_completed_at))
            ) {
                $parentChapter->last_chapter_completed_at = $finalAt;

                $chapterEnd = $parent->paragraph + $parent->chapter_len - 1;
                $parentChapter->completed_chapters = ProgressChapter::where('book', $bookId)
                    ->whereBetween('para', [$parent->paragraph, $chapterEnd])
                    ->where('channel_id', $channelId)
                    ->whereNotNull('completed_at')
                    ->count();

                $parentChapter->save();
            }
        }
    }

    /** 同步章节的标签映射，返回新增 tag 数 */
    private function syncTags(int $bookId, int $para, string $chapterUid): int
    {
        $path = json_decode(
            PaliText::where('book', $bookId)
                ->where('paragraph', $para)
                ->value('path')
        );

        if (! $path) {
            return 0;
        }

        // 收集路径上所有层级的标签
        $tags = [];
        foreach ($path as $value) {
            if ($value->level > 0) {
                $paliTextUuid = PaliText::where('book', $value->book)
                    ->where('paragraph', $value->paragraph)
                    ->value('uid');

                $tagIds = TagMap::where('table_name', 'pali_texts')
                    ->where('anchor_id', $paliTextUuid)
                    ->pluck('tag_id');

                foreach ($tagIds as $tagId) {
                    $tags[$tagId] = 1;
                }
            }
        }

        // 先删后建：重建标签映射
        TagMap::where('table_name', 'progress_chapters')
            ->where('anchor_id', $chapterUid)
            ->delete();

        $count = 0;
        foreach ($tags as $tagId => $_) {
            $tagmap = TagMap::create([
                'table_name' => 'progress_chapters',
                'anchor_id' => $chapterUid,
                'tag_id' => $tagId,
            ]);
            if ($tagmap) {
                $count++;
            }
        }

        return $count;
    }
}
