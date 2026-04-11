<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIAssistant\ArticleTranslateService;

class TestAIArticleTranslate extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:ai.article.translate
     * @var string
     */
    protected $signature = 'test:ai.article.translate';

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
        $service = app(ArticleTranslateService::class);
        // ===== 执行 =====
        $result = $service->setModel('dd81ce6c-e9ff-46b2-b1af-947728ba996e')
            ->translate('2deaf8dd-65b3-4a76-86c4-deec7afabc38')
            ->get();

        // ===== 调试输出（建议保留）=====
        dump($result);
    }
}
