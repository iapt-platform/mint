<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebHookArticleNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:webhookarticlenew {host} {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送消息到一个服务器机器人';

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
		# 获取最新文章数据
		$url = config('app.url')."/api/v2/progress?view=chapter&channel_type=translation";

		$response = Http::get($url);
		if($response->successful()){
			$this->info("get data ok");
			$data = $response['data']['rows'];
			$title = "2022-7-3更新";
			$message = "# wikipali:最新更新\n\n";
			for ($i=0; $i < 4; $i++) {
				# code...
				$row = $data[$i];
				$book = $row['book'];
                $para = $row['para'];
				$channel_id = $row['channel_id'];
				if(!empty($row['title'])){
					$title = str_replace("\n","",$row['title']);
				}else{
					$title = $row['toc'];
				}

				$link = config('app.url')."/app/article/index.php?view=chapter&book={$book}&par={$para}&channel={$channel_id}";
				$message .= "1. [{$title}]({$link})\n";
			}
			$link = config('app.url')."/app/palicanon";
			$message .= "\n [更多]({$link})";
			$this->info($message);
			$url = $this->argument('host');
			switch ($this->argument('type')) {
				case "dingtalk":
					$param = [
						"markdown"=> [
							"title"=> $title,
							"text"=> $message,
						],
						"msgtype"=>"markdown"
						];
					break;
				case "wechat":
					$param = [
						"msgtype"=>"markdown",
						"markdown"=> [
							"content"=> $message,
						],
						];
					break;
			}
			$response = Http::post($url, $param);
		}else{
			$this->error("章节数据获取错误");
		}
        return 0;
    }
}
