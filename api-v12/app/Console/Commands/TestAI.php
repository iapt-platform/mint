<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ai';

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
        $url = 'https://api.moonshot.cn/v1/chat/completions';
        $param = [
                "model" => "moonshot-v1-8k",
                "messages" => [
                    ["role" => "system","content" => "你是 Kimi，由 Moonshot AI 提供的人工智能助手，你更擅长中文和英文的对话。你会为用户提供安全，有帮助，准确的回答。同时，你会拒绝一切涉及恐怖主义，种族歧视，黄色暴力等问题的回答。Moonshot AI 为专有名词，不可翻译成其他语言。"],
                    ["role" => "user","content" => "你好，我叫李雷，1+1等于多少？"],
                ],
                "temperature" => 0.3,
        ];
        $response = Http::withToken('sk-kwjHIMh3PoWwUwQyKdT3KHvNe8Es19SUiujGrxtH09uDQCui')
                        ->post($url,$param);
        if($response->failed()){
            $this->error('http request error'.$response->json('message'));
        }else{
            $this->info(json_encode($response->json()));
        }
        return 0;
    }
}
