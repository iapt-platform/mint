<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebHook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:webhook {listener} {url} {title} {message}';
	protected $url = [
		"dingtalk1"=>"https://oapi.dingtalk.com/robot/send?access_token=34143dbec80a8fc09c1cb5897a5639ee3a9a32ecfe31835ad29bf7013bdb9fdf",
		"weixin1"=>"https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=9693cceb-bd2e-40c9-8c0c-3260b2c50aa8",
	];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送消息到一个服务器机器人,';

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
		switch ($this->argument('listener')) {
			case 'weixin':
				break;
			case 'dingtalk':
				# code...
				$url = $this->url[$this->argument('url')];
				$param = [
				"markdown"=> [
					"title"=> $this->argument('title'),
					"text"=> $this->argument('message'),
				],
				"msgtype"=>"markdown"
				];
				break;
			default:
				# code...
				break;
		}
		$response = Http::post($url, $param);
        return 0;
    }
}
