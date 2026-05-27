<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;


use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Models\Progress;
use Illuminate\Support\Facades\Log;

class UpgradeProgressPara extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:progress --book=152 --channel=19f53a65-81db-4b7d-8144-ac33f1217d34
     * @var string
     */
    protected $signature = 'upgrade:progress.para {--book=} {--para=} {--channel=} {--resume}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (\App\Tools\Tools::isStop()) {
            return 0;
        }
        $this->info('upgrade:progress start');
        $startTime = time();
        $book = $this->option('book');
        $para = $this->option('para');
        $channelId = $this->option('channel');
        if ($channelId) {
            $this->line('channel=' . $channelId);
        }
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
            $sentences = $table->groupby('book_id', 'paragraph', 'channel_uid')
                ->select('book_id', 'paragraph', 'channel_uid');
        } else {
            if ($this->option('resume')) {
                $sentences = Sentence::where('strlen', '>', 0)
                    ->whereBetween('book_id', [$book, 1000])
                    ->where('paragraph', '>=', $para)
                    ->whereNotNull('channel_uid')
                    ->groupby('book_id', 'paragraph', 'channel_uid')
                    ->select('book_id', 'paragraph', 'channel_uid');
            } else {
                $sentences = Sentence::where('strlen', '>', 0)
                    ->where('book_id', '<', 1000)
                    ->whereNotNull('channel_uid')
                    ->groupby('book_id', 'paragraph', 'channel_uid')
                    ->select('book_id', 'paragraph', 'channel_uid');
            }
        }
        $total = DB::query()
            ->fromSub($sentences, 't')
            ->count();
        $sentences = $sentences->cursor();
        $this->info('sentences:' . $total);
        $curr = 0;
        #第二步 更新段落表
        foreach ($sentences as $sentence) {

            # 第二步 生成para progress 1,2,15,zh-tw
            # 计算此段落完成时间
            $finalAt = Sentence::where('strlen', '>', 0)
                ->where('book_id', $sentence->book_id)
                ->where('paragraph', $sentence->paragraph)
                ->where('channel_uid', $sentence->channel_uid)
                ->max('created_at');
            $updateAt = Sentence::where('strlen', '>', 0)
                ->where('book_id', $sentence->book_id)
                ->where('paragraph', $sentence->paragraph)
                ->where('channel_uid', $sentence->channel_uid)
                ->max('updated_at');
            # 查询每个段落的等效巴利语字符数
            $result_sent = Sentence::where('strlen', '>', 0)
                ->where('book_id', $sentence->book_id)
                ->where('paragraph', $sentence->paragraph)
                ->where('channel_uid', $sentence->channel_uid)
                ->select('word_start')
                ->get();

            $paraInfo = [
                'book' => $sentence->book_id,
                'para' => $sentence->paragraph,
                'channel_id' => $sentence->channel_uid
            ];
            if (count($result_sent) > 0) {
                #查询这些句子的总共等效巴利语字符数
                $para_strlen = 0;
                foreach ($result_sent as $sent) {
                    # code...
                    $para_strlen += PaliSentence::where('book', $sentence->book_id)
                        ->where('paragraph', $sentence->paragraph)
                        ->where('word_begin', $sent->word_start)
                        ->value('length');
                }

                $paraData = [
                    'lang' => 'en',
                    'all_strlen' => $para_strlen,
                    'public_strlen' => $para_strlen,
                    'created_at' => $finalAt,
                    'updated_at' => $updateAt,
                ];


                Progress::updateOrInsert($paraInfo, $paraData);
            }
            $curr++;
            if ($curr % 500 === 0) {
                $present = (int)($curr * 100 / $total);
                $this->info("[{$present}%] Progress " . json_encode($paraInfo));
                sleep(1);
            }
        }

        $time = time() - $startTime;
        $this->info("upgrade progress finished in {$time}s");

        return 0;
    }
}
