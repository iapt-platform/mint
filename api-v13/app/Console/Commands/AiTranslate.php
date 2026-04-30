<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Models\PaliText;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiTranslate extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan ai:sentence.translate --type=chapter --api=deepseek --model=deepseek-chat --sid=107-2357
     * php artisan ai:sentence.translate --type=sentence --api=kimi --model=moonshot-v1-8k --sid=107-2357-9-47
     * @var string
     */
    protected $signature = <<<command
    ai:sentence.translate 
    {--type=sentence  : sentence|paragraph|chapter} 
    {--api=  : ai engin url} 
    {--model=  : ai model } 
    {--sid=  : 句子编号 } 
    {--nissaya=  : nissaya channel } 
    {--result=  : result channel } 
    command;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '使用LLM 和nissaya数据翻译句子';

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
        //句子号列表
        $sentences = array();
        $totalLen = 0;
        switch ($this->option('type')) {
            case 'sentence':
                $sentences[] = explode('-', $this->option('sid'));
                break;
            case 'paragraph':
                $para = explode('-', $this->option('sid'));
                $sent = PaliSentence::where('book', $para[0])
                    ->where('paragraph', $para[1])->orderBy('word_begin')->get();
                foreach ($sent as $key => $value) {
                    $sentences[] = [$para[0], $para[1], $value->word_begin, $value->word_end];
                }
                break;
            case 'chapter':
                $para = explode('-', $this->option('sid'));
                $chapterLen = PaliText::where('book', $para[0])
                    ->where('paragraph', $para[1])->value('chapter_len');
                $sent = PaliSentence::where('book', $para[0])
                    ->whereBetween('paragraph', [$para[1], $para[1] + $chapterLen - 1])
                    ->orderBy('paragraph')
                    ->orderBy('word_begin')->get();
                foreach ($sent as $key => $value) {
                    $sentences[] = [$para[0], $para[1], $value->word_begin, $value->word_end];
                }
                break;
            default:
                return 1;
                break;
        }
        //获取句子总长度

        foreach ($sentences as $key => $sentence) {
            $totalLen += $this->sentLen($sentence);
        }
        //
        foreach ($sentences as $key => $sentence) {
            # 获取巴利句子
            $pali = PaliSentence::where('book', $sentence[0])
                ->where('paragraph', $sentence[1])
                ->where('word_begin', $sentence[2])
                ->where('word_end', $sentence[3])
                ->value('text');
            //获取nissaya
            $nissaya = Sentence::where('channel_uid', $this->option('nissaya'))
                ->where('book_id', $sentence[0])
                ->where('paragraph', $sentence[1])
                ->where('word_start', $sentence[2])
                ->where('word_end', $sentence[3])
                ->value('content');
            //获取ai结果
            $api = $this->getEngin($this->option('api'));
            if (!$api) {
                $this->error('ai translate no api');
                return 1;
            }
            $json = $this->fetch($api, $this->option('model'), $pali, $nissaya);
            Log::info('ai translate', ['json' => $json]);
            $this->info($json['choices'][0]['message']['content']);
            //写入
        }
        return 0;
    }

    private function sentLen($id)
    {
        return PaliSentence::where('book', $id[0])
            ->where('paragraph', $id[1])
            ->where('word_begin', $id[2])
            ->where('word_end', $id[3])
            ->value('length');
    }
    private function getEngin($engin)
    {
        $api = config('mint.ai.accounts');
        $selected = array_filter($api, function ($value) use ($engin) {
            return $value['name'] === $engin;
        });
        if (!is_array($selected) || count($selected) === 0) {
            return null;
        }
        return $selected[0];
    }

    private function fetch($api, $model, $origin,  $nissaya = null)
    {
        $prompt = '翻译上面的巴利文为中文';
        if ($nissaya) {
            $prompt = '根据下面的解释，' . $prompt;
        }
        $message = "{$origin}\n\n{$prompt}\n\n{$nissaya}";

        $url = $api['api_url'];
        $param = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => "你是翻译人工智能助手.bhikkhu 为专有名词，不可翻译成其他语言。"],
                ["role" => "user", "content" => $message],
            ],
            "temperature" => 0.3,
            "stream" => false
        ];
        $response = Http::withToken($api['token'])
            ->post($url, $param);
        if ($response->failed()) {
            $this->error('http request error' . $response->json('message'));
            Log::error('http request error', ['data' => $response->json()]);
            return null;
        } else {
            return $response->json();
        }
    }
}
