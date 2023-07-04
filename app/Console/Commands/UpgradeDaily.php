<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;


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
		if(app()->isLocal()==false){
			$this->call('message:webhook',[
				'listener' => 'dingtalk',
				'url' => 'dingtalk1',
				'title' => "后台任务",
				'message' => " wikipali: 每日统计后台任务开始执行。",
			]);
		}

        //巴利原文段落库目录结构改变时运行
        //$this->call('upgrade:palitext');
        #巴利段落标签
        //$this->call('upgrade:palitexttag');

        //更新单词首选意思
        $this->call('upgrade:dict.default.meaning');

        #译文进度
        $this->call('upgrade:progress');
        $this->call('upgrade:progresschapter');
        //社区术语表
        $this->call('upgrade:community.term',['zh-Hans']);

        # 逐词译数据库分析
        $this->call('upgrade:wbwanalyses');

        $time = time()-$start;

		if(app()->isLocal()==false){
			$this->call('message:webhook',[
				'listener' => 'dingtalk',
				'url' => 'dingtalk1',
				'title' => "后台任务",
				'message' => "wikipali: 每日统计后台任务执行完毕。用时{$time}",
			]);
			//发送dingding消息
			$this->call('message:webhookarticlenew',[
				'host' => 'https://oapi.dingtalk.com/robot/send?access_token=34143dbec80a8fc09c1cb5897a5639ee3a9a32ecfe31835ad29bf7013bdb9fdf',
				'type' => 'dingtalk',
			]);
			//发送微信消息
			$this->call('message:webhookarticlenew',[
				'host' => 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=25dbd74f-c89c-40e5-8cbc-48b1ef7710b8',
				'type' => 'wechat',
			]);
		}


        return 0;
    }
}
