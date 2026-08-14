<?php

namespace App\Console\Commands;

use App\Models\DhammaTerm;
use App\Services\OpenSearchService;
use App\Services\TermService;
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
        protected TermService $termService,
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

                $this->indexTerm($term->guid);
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

    /**
     * 构建单条词条文档并写入 OpenSearch
     *
     * 文档结构遵循新版 mapping：
     *   title.text.pali / title.text.zh  → 全文检索
     *   title.suggest.pali / title.suggest.zh → 自动建议
     *   content.text.pali / content.text.zh   → 正文内容
     *
     * @param  string  $id  DhammaTerm 的 guid
     */
    protected function indexTerm(string $id): void
    {
        $termData = $this->termService->find($id, 'text');
        $channelName = $termData['channel']['name'] ?? '';
        $isCommunity = $this->termService->isCommunity($termData['channel_id']);
        $content = $termData['html'] ?? $termData['meaning'];

        $categories = $this->extractCategories($termData['note'] ?? '');
        $quality = $this->extractFirstQuality($termData['note'] ?? '');
        $tags = [];
        foreach ($categories as $key => $category) {
            $tags[] = "category:{$category}";
        }
        if (! empty($quality)) {
            $tags[] = "quality:{$quality}";
        }
        $document = [
            'id' => "term_{$id}",
            'resource_id' => $id,
            'resource_type' => 'term',
            'title' => [
                'text' => [
                    'pali' => $termData['word'],
                    'zh' => $termData['meaning'],
                ],
                'suggest' => [
                    'pali' => [$termData['word']],
                    'zh' => [$termData['meaning']],
                ],
            ],
            'summary' => [
                'text' => $termData['summary'] ?? '',
            ],
            'content' => [],
            'bold_single' => [$termData['meaning'], $termData['word']],
            'related_id' => $termData['word'],
            'category' => null,
            'tags' => $tags,
            'language' => $termData['language'],
            'updated_at' => now()->toIso8601String(),
            'path' => $termData['studio']['realName']."/{$channelName}",
            'metadata' => ['channel' => $termData['channel_id']],
        ];

        // TODO: 补充语言判断，将内容放入对应的 text.pali 或 text.zh 字段
        $plainText = strip_tags($content);
        if (str_contains($termData['language'], 'zh')) {
            $document['content']['text']['zh'] = $plainText;
        } else {
            $document['content']['text']['zh'] = $plainText;
        }
        $document['content']['display'] = $content;             // 展示

        if ($this->isTest) {
            $this->info($document['title']['text']['pali']);
            // $this->info($document['summary']['text']);
        } else {
            $this->openSearchService->create($document['id'], $document);
        }
    }

    /**
     * 提取 Markdown 中的 {{category|...}} 分类标签
     */
    private function extractCategories(string $content): array
    {
        if (empty($content)) {
            return [];
        }
        preg_match_all('/\{\{category\|([^}]+)\}\}/u', $content, $matches);

        return array_values(array_filter(array_map(
            fn ($item) => trim($item),
            $matches[1] ?? []
        )));
    }

    /**
     * 提取 Markdown 中第一个 {{quality|...}} 标签内的内容
     */
    private function extractFirstQuality(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        preg_match('/\{\{quality\|([^}]+)\}\}/u', $content, $matches);

        return isset($matches[1]) ? trim($matches[1]) : '';
    }
}
