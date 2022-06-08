<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        # 刷巴利语句子uuid 仅调用一次
        $this->call('upgrade:palitextid');
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
        return 0;
    }
}
