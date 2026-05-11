<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\AIModelService;
use App\Services\TermService;
use App\Services\AIAssistant\AITermService;
use App\Services\AuthService;

use App\Http\Resources\AiModelResource;


class UpgradeAITerm extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:ai.term  --id=e07bf5b8-bd81-4f0a-9d2c-8e0128b954d7
     * php artisan upgrade:ai.term
     * @var string
     */
    protected $signature = 'upgrade:ai.term {--id=} {--resume} {--model=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected AiModelResource $model;
    protected $modelToken;
    protected $workChannel;
    protected $accessToken;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected AIModelService $modelService,
        protected TermService $termService,
        protected AITermService $aiTermService
    ) {

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('model')) {
            $modelId = $this->ask('请输入 llm model id');
        } else {
            $modelId = $this->option('model');
        }
        $this->aiTermService->setModel($modelId);
        $this->model = $this->modelService->getModelById($modelId);
        $this->info("model:{$this->model['model']}");
        $this->modelToken = AuthService::getUserToken($modelId);

        if ($this->option('id')) {
            $terms = [['guid' => $this->option('id'), 'word' => 'word']];
        } else {
            $terms = $this->termService->getCommunityGlossary('zh-Hans')['items']->toArray();
        }

        foreach ($terms as $key => $term) {
            $this->info("[{$term['word']}] running");
            $result = $this->aiTermService->update($term['guid']);
            $this->info("[{$term['word']}]content " . substr($result, 0, 30));
        }

        return 0;
    }
}
