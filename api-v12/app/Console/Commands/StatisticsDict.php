<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\UserOperationLog;

class StatisticsDict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:dict';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计 字典 每月查询';

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
        $file = "public/statistics/lookup-monthly.csv";
        Storage::disk('local')->put($file, "");
        #按月获取数据
        $firstDay = UserOperationLog::where('op_type','dict_lookup')
                            ->orderBy('created_at')
                            ->select('created_at')
                            ->first();
        $firstDay = strtotime($firstDay->created_at);
        $firstMonth = Carbon::create(date("Y-m",$firstDay));
        $now = Carbon::now();
        $current = $firstMonth;
        $sumCount = 0;
        while ($current <= $now) {
            # code...
            $start = Carbon::create($current)->startOfMonth();
            $end = Carbon::create($current)->endOfMonth();
            $date = $current->format('Y-m');
            $count = UserOperationLog::where('op_type','dict_lookup')
                              ->whereDate('created_at','>=',$start)
                              ->whereDate('created_at','<=',$end)
                              ->count();
            $sumCount += $count;
            $editor = UserOperationLog::where('op_type','dict_lookup')
                              ->whereDate('created_at','>=',$start)
                              ->whereDate('created_at','<=',$end)
                              ->groupBy('user_id')
                              ->select('user_id')->get();
            $info = $date.','.$count.','.$sumCount.','.count($editor);
            $this->info($info);
            Storage::disk('local')->append($file, $info);
            $current->addMonth(1);
        }
        return 0;
    }
}
