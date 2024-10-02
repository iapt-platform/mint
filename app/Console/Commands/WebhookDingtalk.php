<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDingtalk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:dingtalk  {url} {title} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send webhook message to dingtalk';

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
        $url = $this->argument('url');
        $param = ["markdown"=> [
                        "title"=> $this->argument('title'),
                        "text"=> $this->argument('message'),
                    ],
                    "msgtype"=>"markdown"
                ];

        $this->info("url={$url}");
        try{
            $response = Http::post($url, $param);
            if($response->successful()){
                return 0;
            }else{
                return 1;
            }
        }catch(\Exception $e){
            Log::error($e);
            return 1;
        }
        return 0;
    }
}
