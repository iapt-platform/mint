<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AiTranslateService;

class AiTranslate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;       // 最大尝试次数（重试次数 = 3 - 1）
    public $timeout = 60;    // 超时时间，单位：秒
    private $aiService;
    private $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        echo 'ai worker start';
        $data = json_decode(json_encode($this->data['payload']));
        $this->aiService = app(AiTranslateService::class);
        return $this->aiService->processTranslate($this->data['message_id'], $data, null);
    }
}
