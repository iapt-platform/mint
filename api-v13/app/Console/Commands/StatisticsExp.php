<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\UserOperationDaily;

class StatisticsExp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:exp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计 经验值 每月查询';

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
        $file = "public/statistics/exp-monthly.csv";
        Storage::disk('local')->put($file, "");
        #按月获取数据
        $firstDay = UserOperationDaily::select('created_at')
                            ->orderBy('created_at')
                            ->first();
        $firstDay = strtotime($firstDay->created_at);
        $firstMonth = Carbon::create(date("Y-m",$firstDay));
        $now = Carbon::now();
        $current = $firstMonth;
        $sumTime = 0;
        while ($current <= $now) {
            # code...
            $start = Carbon::create($current)->startOfMonth();
            $end = Carbon::create($current)->endOfMonth();
            $date = $current->format('Y-m');
            $time = UserOperationDaily::whereDate('created_at','>=',$start)
                              ->whereDate('created_at','<=',$end)
                              ->sum('duration')/1000;
            $sumTime += $time;
            $editor = UserOperationDaily::whereDate('created_at','>=',$start)
                              ->whereDate('created_at','<=',$end)
                              ->groupBy('user_id')
                              ->select('user_id')->get();
            $info = $date.','.(int)($time/3600).','.(int)($sumTime/3600).','.count($editor);
            $this->info($info);
            Storage::disk('local')->append($file, $info);
            $current->addMonth(1);
        }
        return 0;
    }
}
