<?php

namespace App\Console\Commands;

use App\Services\AIAssistant\AITermService;
use Illuminate\Console\Command;

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

        // ===== 执行 =====
        $result = $service->setModel('dd81ce6c-e9ff-46b2-b1af-947728ba996e')
            ->update('f3ba16e5-862d-49c4-b5b0-39ab8b8ca4f4');

        // ===== 调试输出（建议保留）=====
        dump($result);
    }
}
