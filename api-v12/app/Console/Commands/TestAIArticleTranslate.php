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
    protected $signature = 'test:ai.article.translate {--article=} {--anthology=} {--model=}  {--channel=}';

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
        if (
            !$this->option('model') ||
            !$this->option('channel')
        ) {
            $this->error('model,article,channel is requested');
            return;
        }
        //
        // ===== 创建 Service =====
        $service = app(ArticleTranslateService::class);
        // ===== 执行 =====
        if ($this->option('article')) {
            $this->info('article translate start');
            $total = $service->setModel($this->option('model'))
                ->setChannel($this->option('channel'))
                ->translateArticle($this->option('article'))
                ->save();
            $this->info("{$total} sentences saved");
        }
        if ($this->option('anthology')) {
            $this->info('anthology translate start');
            $total = $service->setModel($this->option('model'))
                ->setChannel($this->option('channel'))
                ->translateAnthology($this->option('anthology'), function ($article, $sentences) {
                    $this->info("translate article {$article} sentences {$sentences}");
                });
            $this->info("{$total} article saved");
        }
    }
}
