<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\SentHistory;
use App\Models\Sentence;
use App\Models\ProgressChapter;
use App\Models\PaliText;
use Illuminate\Support\Facades\Cache;

class UpgradeChapterDynamicWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:chapter.dynamic.weekly {--test} {--book=} {--offset=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新章节活跃程度svg';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
		$this->info('upgrade:chapter.dynamic.weekly start.');

        $startAt = time();
        $weeks = 60; //统计多少周

//更新总动态
		$this->info("更新总动态");
        $table = ProgressChapter::select('book','para')
                                    ->groupBy('book','para')
                                    ->orderBy('book');
        if($this->option('book')){
            $table = $table->where('book',$this->option('book'));
        }
        $chapters = $table->get();
        $bar = $this->output->createProgressBar(count($chapters));

        foreach ($chapters as $key => $chapter) {
            #章节长度
            $paraEnd = PaliText::where('book',$chapter->book)
                            ->where('paragraph',$chapter->para)
                            ->value('chapter_len')+$chapter->para-1;

            $progress = [];
            for ($i=$weeks; $i > 0 ; $i--) {
                #这一周有多少次更新
                $currDay = $i*7+$this->option('offset',0);
                $count = SentHistory::whereBetween('sent_histories.created_at',
                                                  [Carbon::today()->subDays($currDay)->startOfWeek(),
                                                  Carbon::today()->subDays($currDay)->endOfWeek()])
                           ->leftJoin('sentences', 'sent_histories.sent_uid', '=', 'sentences.uid')
                             ->where('book_id',$chapter->book)
                             ->whereBetween('paragraph',[$chapter->para,$paraEnd])
                             ->count();
                $progress[] = $count;
            }
            $key="/chapter_dynamic/{$chapter->book}/{$chapter->para}/global";
            Cache::put($key,$progress,3600*24*7);

            $bar->advance();
            sleep(2);

            if($this->option('test')){
                $this->info("key:{$key}");
                if(Cache::has($key)){
                    $this->info('has key '.$key);
                }
                break; //调试代码
            }
        }
        $bar->finish();

		$time = time()- $startAt;
        $this->info("用时 {$time}");

        $startAt = time();

		$startAt = time();
        //更新chennel动态
        $this->info('更新chennel动态');

        $table = ProgressChapter::select('book','para','channel_id');
        if($this->option('book')){
            $table = $table->where('book',$this->option('book'));
        }
        $bar = $this->output->createProgressBar($table->count());

        foreach ($table->cursor() as $chapter) {
            # code...
            $max=0;
            #章节长度
            $paraEnd = PaliText::where('book',$chapter->book)
                            ->where('paragraph',$chapter->para)
                            ->value('chapter_len')+$chapter->para-1;
            $progress = [];
            for ($i=$weeks; $i > 0 ; $i--) {
                #这一周有多少次更新
                $currDay = $i*7+$this->option('offset',0);
                $count = SentHistory::whereBetween('sent_histories.created_at',
                                                [Carbon::today()->subDays($currDay)->startOfWeek(),
                                                Carbon::today()->subDays($currDay)->endOfWeek()])
                           ->leftJoin('sentences', 'sent_histories.sent_uid', '=', 'sentences.uid')
                             ->where('book_id',$chapter->book)
                             ->whereBetween('paragraph',[$chapter->para,$paraEnd])
                             ->where('sentences.channel_uid',$chapter->channel_id)
                             ->count();
                $progress[] = $count;
            }
            $key="/chapter_dynamic/{$chapter->book}/{$chapter->para}/ch_{$chapter->channel_id}";
            Cache::put($key,$progress,3600*24*7);
            $bar->advance();
            sleep(2);

            if($this->option('test')){
                $this->info("key:{$key}");
                if(Cache::has($key)){
                    $this->info('has key '.$key);
                }
                break; //调试代码
            }
        }
        $bar->finish();
		$time = time()- $startAt;
        $this->info("用时 {$time}");

        $this->info("upgrade:chapter.dynamic done");

        return 0;
    }
}
