<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpgradeWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '周更';

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
        $currTime = time();
        #译文进度
        $this->call('upgrade:progress');
        $time = time()-$currTime;
        $message = "progress time:{$time}; ";
        $this->info($message);
        $currTime = time();

        $this->call('upgrade:progress.chapter');
        $time = time()-$currTime;
        $message = "progress.chapter time:{$time}; ";
        $this->info($message);
        $currTime = time();

        # 逐词译数据库分析
        $this->call('upgrade:wbw.analyses');
        $time = time()-$currTime;
        $message = "wbw.analyses:{$time}; ";
        $this->info($message);

        # 段落更新图
        $this->call('upgrade:chapter.dynamic');
        $this->call('upgrade:chapter.dynamic.weekly');
        return 0;
    }
}
