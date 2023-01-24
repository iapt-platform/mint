<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use App\Models\Sentence;
use Illuminate\Support\Facades\Storage;

class StatisticsNissaya extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:nissaya';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计nissaya 每日录入进度';

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
        $nissaya_channel = Channel::where('type','nissaya')->select('uid')->get();
        $channels = [];
        foreach ($nissaya_channel as $key => $value) {
            # code...
            $channels[] = $value->uid;
        }
        $this->info('channel:'.count($channels));
        $maxDay = 360;
        $file = "public/export/nissaya-daily.csv";
        Storage::disk('local')->put($file, "");
        #按天获取数据
        for($i = 1; $i <= $maxDay; $i++){
            $day = strtotime("today -{$i} day");
            $date = date("Y-m-d",$day);
            $strlen = Sentence::whereIn('channel_uid',$channels)
                    ->whereDate('created_at','=',$date)
                    ->sum('strlen');
            $editor = Sentence::whereIn('channel_uid',$channels)
                    ->whereDate('created_at','=',$date)
                    ->groupBy('editor_uid')
                    ->select('editor_uid')->get();
            $info = $date.','.$strlen.','.count($editor);
            $this->info($info);
            Storage::disk('local')->append($file, $info);
        }
        $file = "public/export/nissaya-week.csv";
        Storage::disk('local')->put($file, "");
        for($i = 1; $i <= $maxDay; $i=$i+7){
            $day1 = strtotime("today -{$i} day");
            $date1 = date("Y-m-d",$day1);
            $j = $i - 7;
            $date2 = date("Y-m-d",strtotime("today -{$j} day"));
            $editor = Sentence::whereIn('channel_uid',$channels)
                    ->whereDate('created_at','>',$date1)
                    ->whereDate('created_at','<=',$date2)
                    ->groupBy('editor_uid')
                    ->select('editor_uid')->get();
            $info = $date2.','.$date1.','.count($editor);
            $this->info($info);
            Storage::disk('local')->append($file, $info);
        }
        return 0;
    }
}
