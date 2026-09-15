<?php

namespace App\Console\Commands;

use App\Services\AIAssistant\ArticleTranslateService;
use App\Services\ArticleService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:ai-article-translate
{--article=}
{--anthology=}
{--model=}
{--channel=}
{--thinking=} : deepseek deep thinking true or false
{--token=}
{--endpoint=}')]
#[Description('translate article by ai ')]
class AiArticleTranslate extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (
            ! $this->option('model') ||
            ! $this->option('channel')
        ) {
            $this->error('model,article,channel is requested');

            return;
        }
        //
        // ===== 创建 Service =====
        $service = app(ArticleTranslateService::class);
        $articleService = app(ArticleService::class);
        // ===== 执行 =====
        if ($this->option('article')) {
            $this->info('article translate start');
            $llm = $service->setModel($this->option('model'))
                ->setChannel($this->option('channel'));

            if ($this->option('thinking')) {
                $llm = $llm->setThinking($this->option('thinking') === 'true');
            }

            $total = $llm->translateArticle($this->option('article'))
                ->save();
            $this->info("{$total} sentences saved");
        }
        if ($this->option('anthology')) {
            $this->info('anthology translate start');
            $articleIds = $articleService->articlesInAnthology($this->option('anthology'));

            foreach ($articleIds as $article) {
                $this->info('article translate start');
                $llm = $service->setModel($this->option('model'))
                    ->setChannel($this->option('channel'));

                if ($this->option('thinking')) {
                    $llm = $llm->setThinking($this->option('thinking') === 'true');
                }

                $total = $llm->translateArticle($article)
                    ->save();
                $this->info("{$total} sentences saved");
            }

            $this->info(count($articleIds).' article saved');
        }
    }
}
