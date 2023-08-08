<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
    protected $description = 'Command description';

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
        $url = $this->argument('url');
        $this->info("url={$url}");
        $param = ["markdown"=> [
                        "title"=> $this->argument('title'),
                        "text"=> $this->argument('message'),
                    ],
                    "msgtype"=>"markdown"
                ];
        $response = Http::post($url, $param);
        return 0;
    }
}
