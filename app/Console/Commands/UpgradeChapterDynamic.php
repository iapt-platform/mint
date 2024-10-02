<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\SentHistory;
use App\Models\Sentence;
use App\Models\ProgressChapter;
use App\Models\PaliText;

class UpgradeChapterDynamic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:chapter.dynamic {--test}';

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
		$this->info('upgrade:chapterdynamic start.');

        $startAt = time();
        $img_width = 600;
        $img_height = 120;
        $days = 300; //统计多少天
        $min = 30;
        $linewidth = 2;


//更新总动态
		$this->info("更新总动态");
        $chapters = ProgressChapter::select('book','para')
                                    ->groupBy('book','para')
                                    ->orderBy('book')
                                    ->get();
        $bar = $this->output->createProgressBar(count($chapters));
        foreach ($chapters as $key => $chapter) {
            # code...
            $max=0;
            #章节长度
            $paraEnd = PaliText::where('book',$chapter->book)
                            ->where('paragraph',$chapter->para)
                            ->value('chapter_len')+$chapter->para-1;

            $svg = "<svg xmlns='http://www.w3.org/2000/svg'  fill='currentColor' viewBox='0 0 $img_width $img_height'>";
            $svg .= "<polyline points='";
            for ($i=$days; $i >0 ; $i--) {
                # code...

                #这一天有多少次更新
                $count = SentHistory::whereDate('sent_histories.created_at', '=', Carbon::today()->subDays($i)->toDateString())
                           ->leftJoin('sentences', 'sent_histories.sent_uid', '=', 'sentences.uid')
                             ->where('book_id',$chapter->book)
                             ->whereBetween('paragraph',[$chapter->para,$paraEnd])
                             ->count();
                $x=($days-$i)*($img_width/$days);
                $y=(300-$count)*($img_height/300)-$linewidth;
                $svg .= "{$x},{$y} ";
            }
            $svg .= "'  style='fill:none;stroke:green;stroke-width:{$linewidth}' /></svg>";
            $filename = "{$chapter->book}/{$chapter->para}/globle.svg";
            Storage::disk('local')->put("public/images/chapter_dynamic/{$filename}", $svg);
            $bar->advance();

            if($this->option('test')){
                break; //调试代码
            }

        }
        $bar->finish();

		$time = time()- $startAt;
        $this->info("用时 {$time}");

        $startAt = time();

        $this->info('更新缺的章节空白图');
        // 更新缺的章节空白图
        $chapters = PaliText::select('book','paragraph')
                            ->where('level', '<', 8)
                            ->get();
        $bar = $this->output->createProgressBar(count($chapters));
        $svg = "<svg xmlns='http://www.w3.org/2000/svg'  fill='currentColor' viewBox='0 0 $img_width $img_height'></svg>";
        foreach ($chapters as $key => $chapter) {
            $filename = "{$chapter->book}/{$chapter->paragraph}/globle.svg";
            if(!Storage::disk('local')->exists("public/images/chapter_dynamic/{$filename}")){
                Storage::disk('local')->put("public/images/chapter_dynamic/{$filename}", $svg);
            }
            $bar->advance();

            if($this->option('test')){
                break; //调试代码
            }
        }
        $bar->finish();
		$time = time()- $startAt;
        $this->info("用时 {$time}");

		$startAt = time();
        //更新chennel动态
        $this->info('更新chennel动态');
        $bar = $this->output->createProgressBar(ProgressChapter::count());

        foreach (ProgressChapter::select('book','para','channel_id')->cursor() as $chapter) {
            # code...
            $max=0;
            #章节长度
            $paraEnd = PaliText::where('book',$chapter->book)
                            ->where('paragraph',$chapter->para)
                            ->value('chapter_len')+$chapter->para-1;

            $svg = "<svg xmlns='http://www.w3.org/2000/svg'  fill='currentColor' viewBox='0 0 $img_width $img_height'>";
            $svg .= "<polyline points='";
            for ($i=$days; $i >0 ; $i--) {
                # code...

                #这一天有多少次更新
                $count = SentHistory::whereDate('sent_histories.created_at', '=', Carbon::today()->subDays($i)->toDateString())
                           ->leftJoin('sentences', 'sent_histories.sent_uid', '=', 'sentences.uid')
                             ->where('book_id',$chapter->book)
                             ->whereBetween('paragraph',[$chapter->para,$paraEnd])
                             ->where('sentences.channel_uid',$chapter->channel_id)
                             ->count();
                $x=($days-$i)*($img_width/$days);
                $y=(300-$count)*($img_height/300)-$linewidth;
                $svg .= "{$x},{$y} ";
            }
            $svg .= "'  style='fill:none;stroke:green;stroke-width:{$linewidth}' /></svg>";
            $filename = "{$chapter->book}/{$chapter->para}/ch_{$chapter->channel_id}.svg";
            Storage::disk('local')->put("public/images/chapter_dynamic/{$filename}", $svg);
            $bar->advance();

            if($this->option('test')){
                break; //调试代码
            }
        }
        $bar->finish();
		$time = time()- $startAt;
        $this->info("用时 {$time}");

        $this->info("upgrade:chapterdynamic done");

        return 0;
    }
}
