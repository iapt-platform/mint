<?php

namespace App\Console\Commands;

use App\Http\Resources\AiModelResource;
use App\Services\AIAssistant\AITermService;
use App\Services\AIModelService;
use App\Services\AuthService;
use App\Services\TermService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpgradeAITerm extends Command
{
    // 断点缓存键：记录 channel 模式下最后一个已完成的术语游标 ['index'=>int,'word'=>string]，
    // 使命令可重入——中断后重跑自动跳过已完成的术语
    private const CURSOR_KEY = 'upgrade:ai.term:cursor';

    /**
     * The name and signature of the console command.
     * php artisan upgrade:ai.term {model} --id=e07bf5b8-bd81-4f0a-9d2c-8e0128b954d7
     * php artisan upgrade:ai.term {model} --channel={channelId}
     *
     * @var string
     */
    protected $signature = 'upgrade:ai.term
    {model : LLM 模型 id}
    {--word= : 保留参数}
    {--channel= : 遍历术语表，逐词生成/更新到该 channel}
    {--id= : 仅处理该术语 guid}
    {--thinking= : 开启/关闭 deepseek thinking，true | false}
    {--fresh : 清除断点缓存，从头开始}';

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

    protected ?bool $thinking = null;

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
        $modelId = $this->argument('model');

        $this->aiTermService->setModel($modelId);
        $this->model = $this->modelService->getModelById($modelId);
        $this->info("model:{$this->model['model']}");
        $this->modelToken = AuthService::getUserToken($modelId);

        if ($this->option('thinking') !== null) {
            $this->thinking = $this->option('thinking') === 'true';
        }
        $this->aiTermService->setThinking($this->thinking);

        if ($this->option('fresh')) {
            Cache::forget(self::CURSOR_KEY);
            $this->info('cleared checkpoint');
        }

        if ($this->option('id')) {
            $this->info($this->option('id').' running');
            $result = $this->aiTermService->update($this->option('id'));
            $this->info('done content '.substr($result, 0, 30));
        } elseif ($this->option('channel')) {
            $this->runChannel($this->option('channel'));
        } else {
            $this->error('id or channel is request');

            return 1;
        }

        return 0;
    }

    /**
     * 遍历术语表，逐词生成/更新到指定 channel；逐词写入断点以支持重入。
     *
     * 断点游标记录最后一个已完成术语的 word 与 index；重跑时优先按 word 在当前术语表中
     * 定位续跑位置（术语表尾部新增也能正确接续），找不到则回退到 index。
     */
    private function runChannel(string $channelId): void
    {
        $terms = $this->termService->getGlossary();
        $total = count($terms);
        $this->info("{$total} terms");

        $startIndex = 0;
        $cursor = Cache::get(self::CURSOR_KEY, []);
        if (! empty($cursor)) {
            $pos = array_search($cursor['word'] ?? null, $terms, true);
            $startIndex = $pos !== false ? (int) $pos + 1 : (int) ($cursor['index'] ?? -1) + 1;
            $this->info("resume from index {$startIndex} (after word [{$cursor['word']}])");
        }

        for ($key = $startIndex; $key < $total; $key++) {
            $term = $terms[$key];
            $this->info("[{$key}/{$total}][{$term}] running");
            $result = $this->aiTermService->updateOrCreate($channelId, $term);
            $this->info("[{$term}]content ".substr($result, 0, 30));

            // 该术语完成后写入断点（中途中断时不会误标记未完成的术语）
            Cache::put(self::CURSOR_KEY, ['index' => $key, 'word' => $term], now()->addHours(48));
        }

        // 全部完成，清空断点
        Cache::forget(self::CURSOR_KEY);
        $this->info('all terms completed');
    }
}
