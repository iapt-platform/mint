<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use App\Models\Sentence;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StatisticsNissaya extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan statistics:nissaya
     * @var string
     */
    protected $signature = 'statistics:nissaya';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计nissaya 每月录入进度';

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
        $nissaya_channels = Channel::where('type','nissaya')->select('uid')->get();
        $this->info('channel:'.count($nissaya_channels));
        $file = "public/statistics/nissaya-monthly.csv";
        $this->info('path='.$file);
        Storage::disk('local')->put($file, "");
        #按月获取数据
        $firstDay = Sentence::whereIn('channel_uid',$nissaya_channels)
                            ->orderBy('created_at')
                            ->select('created_at')
                            ->first();
        $firstDay = strtotime($firstDay->created_at);
        $firstMonth = Carbon::create(date("Y-m",$firstDay));
        $now = Carbon::now();
        $current = $firstMonth;
        $sumStrlen = 0;
        while ($current <= $now) {
            # code...
            $start = Carbon::create($current)->startOfMonth();
            $end = Carbon::create($current)->endOfMonth();
            $date = $current->format('Y-m');
            $strlen = Sentence::whereIn('channel_uid',$nissaya_channels)
                              ->whereDate('created_at','>=',$start)
                              ->whereDate('created_at','<=',$end)
                              ->sum('strlen');
            $sumStrlen += $strlen;
            $editor = Sentence::whereIn('channel_uid',$nissaya_channels)
                              ->whereDate('created_at','>=',$start)
                              ->whereDate('created_at','<=',$end)
                              ->groupBy('editor_uid')
                              ->select('editor_uid')->get();
            $info = $date.','.$strlen.','.$sumStrlen.','.count($editor);
            $this->info($info);
            Storage::disk('local')->append($file, $info);
            $current->addMonth(1);
        }
        return 0;
    }
}
