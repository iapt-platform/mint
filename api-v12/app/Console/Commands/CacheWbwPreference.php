<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\WbwAnalysis;
use Illuminate\Support\Facades\DB;
use App\Tools\RedisClusters;

class CacheWbwPreference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:wbw.preference {--editor=} {--view=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '逐词解析的首选项预热';

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
        $prefix = 'wbw-preference';
        if($this->option('view')==='all' ||
           $this->option('view')==='my'){
            $this->info('个人数据');
            /**
             * 个人数据算法
             * 最新优先
             */
            $wbw = WbwAnalysis::select(['wbw_word','type','editor_id']);
            $wbwCount = DB::select('SELECT count(*) from (
                SELECT wbw_word,type,editor_id from wbw_analyses group by wbw_word,type,editor_id) T');
            if($this->option('editor')){
                $wbw = $wbw->where('editor_id',$this->option('editor'));
                $wbwCount = DB::select('SELECT count(*) from (
                    SELECT wbw_word,type,editor_id from wbw_analyses where editor_id=? group by wbw_word,type,editor_id) T',
                    [$this->option('editor')]);
            }
            $wbw = $wbw->groupBy(['wbw_word','type','editor_id'])->cursor();
            $bar = $this->output->createProgressBar($wbwCount[0]->count);
            $count = 0;
            foreach ($wbw as $key => $value) {
                $data = WbwAnalysis::where('wbw_word',$value->wbw_word)
                                    ->where('type',$value->type)
                                    ->where('editor_id',$value->editor_id)
                                    ->orderBy('updated_at','desc')
                                    ->value('data');
                RedisClusters::put("{$prefix}/{$value->wbw_word}/{$value->type}/{$value->editor_id}",$data);
                $bar->advance();
                $count++;
                if($count%1000 === 0){
                    if(\App\Tools\Tools::isStop()){
                        return 0;
                    }
                }
            }
            $bar->finish();
        }

        if($this->option('view')==='all' ||
           $this->option('view')==='community'
           ){
            $this->info('社区通用');
            /**
             * 社区数据算法
             * 多的优先
             */
            $wbw = WbwAnalysis::select(['wbw_word','type']);
            $count = DB::select('SELECT count(*) from (
                SELECT wbw_word,type from wbw_analyses group by wbw_word,type) T');
            $wbw = $wbw->groupBy(['wbw_word','type'])->cursor();

            $bar = $this->output->createProgressBar($count[0]->count);
            foreach ($wbw as $key => $value) {
                $data = WbwAnalysis::where('wbw_word',$value->wbw_word)
                                    ->where('type',$value->type)
                                    ->selectRaw('data,count(*)')
                                    ->groupBy("data")
                                    ->orderBy("count", "desc")
                                    ->first();

                Cache::put("{$prefix}/{$value->wbw_word}/{$value->type}/0",$data->data);
                $bar->advance();
            }
            $bar->finish();
        }

        return 0;
    }
}
