<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AITermService;

class TestAITerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ai.term';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        // ===== 创建 Service =====
        $service = app(AITermService::class);
        $service->setModel('70259783-6053-4a6e-adda-506031b0cc22');

        // ===== 执行 =====
        $result = $service->create('f3ba16e5-862d-49c4-b5b0-39ab8b8ca4f4');

        // ===== 调试输出（建议保留）=====
        dump($result);
    }
}
