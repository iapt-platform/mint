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

        $message = "wikipali: 每日统计后台任务执行完毕。";
        //巴利原文段落库目录结构改变时运行
        //$this->call('upgrade:palitext');
        #巴利段落标签
        //$this->call('upgrade:palitexttag');

        //更新单词首选意思
        $this->call('upgrade:dict.default.meaning');
        $time = time()-$start;
        $message .= "dict.default.meaning:{$time}; ";
        $currTime = time();

        //社区术语表
        $this->call('upgrade:community.term',['lang'=>'zh-Hans']);
        $time = time()-$currTime;
        $message .= "community.term:{$time}; ";
        $currTime = time();

        /*
        #译文进度
        $this->call('upgrade:progress');
        $time = time()-$currTime;
        $message .= "progress:{$time}; ";
        $currTime = time();

        $this->call('upgrade:progress.chapter');
        $time = time()-$currTime;
        $message .= "progress.chapter:{$time}; ";
        $currTime = time();

        # 逐词译数据库分析
        $this->call('upgrade:wbw.analyses');
        $time = time()-$currTime;
        $message .= "wbw.analyses:{$time}; ";
*/
        # 导出离线数据
        $this->call('export:offline',['format'=>'lzma']);
        $time = time()-$currTime;
        $message .= "export:offline:{$time}; ";

        $time = time()-$start;
        $message .= "总时间:{$time}; ";

		if(app()->isLocal()==false){
			$this->call('message:webhook',[
				'listener' => 'dingtalk',
				'url' => 'dingtalk1',
				'title' => "后台任务",
				'message' => $message,
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
