<?php

namespace App\Console\Commands;

use App\Models\DhammaTerm;
use App\Services\OpenSearchService;
use App\Services\TermIndexService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IndexTerm extends Command
{
    protected $signature = 'opensearch:index-term
        {--test}
        {--word= : 指定单个词条进行索引，省略则索引全部}
        {--fresh : 清除缓存断点，从头开始}';

    protected $description = 'Index Term data into OpenSearch（可重入：中断后重跑自动跳过已索引的词条）';

    // 缓存键：记录最后成功索引的游标位置，48h 过期
    private const CACHE_KEY = 'index-term:cursor';

    private bool $isTest = false;

    public function __construct(
        protected OpenSearchService $openSearchService,
        protected TermIndexService $termIndexService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $word = $this->option('word');

        if ($this->option('test')) {
            $this->isTest = true;
            $this->info('test mode');
        }

        if ($this->option('fresh')) {
            Cache::forget(self::CACHE_KEY);
            $this->info('Cleared cached cursor.');
        }

        try {
            [$connected, $message] = $this->openSearchService->testConnection();
            if (! $connected) {
                $this->error($message);
                Log::error($message);

                return 1;
            }

            // 按自增 id 排序，保证游标稳定（updated_at 可能在运行中被修改）
            $terms = DhammaTerm::select(['id', 'guid', 'word'])->orderBy('id');

            if ($word) {
                $terms = $terms->where('word', $word);
            }

            // 从缓存恢复断点：跳过上次已处理的记录
            $lastId = Cache::get(self::CACHE_KEY);
            if ($lastId && ! $word) {
                $terms = $terms->where('id', '>', $lastId);
                $this->info("Resuming after id={$lastId}");
            }

            $total = $terms->count();
            $this->info("terms to index: {$total}");

            $curr = 0;

            foreach ($terms->cursor() as $term) {
                $curr++;
                if ($curr % 10 === 0) {
                    $percent = (int) ($curr * 100 / $total);
                    $this->info("[{$percent}%]-{$curr}/{$total}  {$term->word}");

                    // 每 10 条保存一次断点
                    Cache::put(self::CACHE_KEY, $term->id, now()->addHours(48));
                }

                if ($this->isTest) {
                    $document = $this->termIndexService->buildDocument($term->guid);
                    $this->info($document['title']['text']['pali']);
                } else {
                    $this->termIndexService->index($term->guid);
                }
            }

            // 全部完成，清除断点缓存
            Cache::forget(self::CACHE_KEY);
            $this->info("index-term finished. total: {$curr}");

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to index Term data: '.$e->getMessage());
            Log::error('Failed to index Term data', ['error' => $e]);

            return 1;
        }
    }
}
