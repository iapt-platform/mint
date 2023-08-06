<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Models\Progress;
use App\Models\ProgressChapter;
use App\Models\PaliText;

class UpgradeProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:progress {--book=} {--para=} {--channel=}';

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
		$this->info('upgrade:progress start');
		$startTime = time();
        $book = $this->option('book');
        $para = $this->option('para');
        $channelId = $this->option('channel');
        if($book && $para && $channelId){
            $channels = Sentence::where('strlen','>',0)
                          ->where('book_id',$book)
                          ->where('paragraph',$para)
                          ->where('channel_uid',$channelId)
                          ->groupby('book_id','paragraph','channel_uid')
                          ->select('book_id','paragraph','channel_uid')
                          ->cursor();
        }else{
            $channels = Sentence::where('strlen','>',0)
                          ->where('book_id','<',1000)
                          ->where('channel_uid','<>','')
                          ->groupby('book_id','paragraph','channel_uid')
                          ->select('book_id','paragraph','channel_uid')
                          ->cursor();
        }

        $this->info('channels:',count($channels));
        #第二步 更新段落表
        $bar = $this->output->createProgressBar(count($channels));
        foreach ($channels as $channel) {
            # 第二步 生成para progress 1,2,15,zh-tw
            # 计算此段落完成时间
            $finalAt = Sentence::where('strlen','>',0)
                        ->where('book_id',$channel->book_id)
                        ->where('paragraph',$channel->paragraph)
                        ->where('channel_uid',$channel->channel_uid)
                        ->max('created_at');
            $updateAt = Sentence::where('strlen','>',0)
                        ->where('book_id',$channel->book_id)
                        ->where('paragraph',$channel->paragraph)
                        ->where('channel_uid',$channel->channel_uid)
                        ->max('updated_at');
            # 查询每个段落的等效巴利语字符数
            $result_sent = Sentence::where('strlen','>',0)
                                    ->where('book_id',$channel->book_id)
                                    ->where('paragraph',$channel->paragraph)
                                    ->where('channel_uid',$channel->channel_uid)
                                    ->select('word_start')
                                    ->get();
            if (count($result_sent) > 0) {
                #查询这些句子的总共等效巴利语字符数
                $para_strlen = 0;
                foreach ($result_sent as $sent) {
                    # code...
                    $para_strlen += PaliSentence::where('book',$channel->book_id)
                                ->where('paragraph',$channel->paragraph)
                                ->where('word_begin',$sent->word_start)
                                ->value('length');
                }

                Progress::updateOrInsert(
                    [
                        'book'=>$channel->book_id,
                        'para'=>$channel->paragraph,
                        'channel_id'=>$channel->channel_uid
                    ],
                    [
                        'lang'=>'en',
                        'all_strlen'=>$para_strlen,
                        'public_strlen'=>$para_strlen,
                        'created_at'=>$finalAt,
                        'updated_at'=>$updateAt,
                    ]);
            }
            $bar->advance();
        }
        $bar->finish();

		$time = time() - $startTime;
		$this->info("upgrade progress finished in {$time}s");

        return 0;
    }
}
