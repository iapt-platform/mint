<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class UpgradeDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天的任务';

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
        $start = time();
        # 刷巴利语句子uuid 仅调用一次
        //$this->call('upgrade:palitextid');
        //巴利原文段落库目录结构改变时运行
        $this->call('upgrade:palitext'); 
        #巴利段落标签
        $this->call('upgrade:palitexttag');
        #译文进度
        $this->call('upgrade:progress');
        $this->call('upgrade:progresschapter');
        # 段落更新图
        $this->call('upgrade:chapterdynamic');
        # 逐词译数据库分析
        $this->call('upgrade:wbwanalyses');

        $time = time()-$start;
        $url = "https://oapi.dingtalk.com/robot/send?access_token=34143dbec80a8fc09c1cb5897a5639ee3a9a32ecfe31835ad29bf7013bdb9fdf";
        $param = [
        "markdown"=> [
            "title"=> "后台任务", 
            "text"=> " wikipali: 后台任务成功执行。用时{$time}", 
        ], 
        "msgtype"=>"markdown"
        ];

        $response = Http::post($url, $param);

        return 0;
    }
}
