<?php

namespace App\Console\Commands;

use App\Models\DhammaTerm;
use Illuminate\Console\Command;
use App\Services\OpenSearchService;
use App\Services\TermService;
use Illuminate\Support\Facades\Log;

class IndexTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * @example
     *   php artisan opensearch:index-term
     *   php artisan opensearch:index-term --word=anomadassī
     *   php artisan opensearch:index-term --test
     */
    protected $signature = 'opensearch:index-term
        {--test}
        {--word= : 指定单个词条进行索引，省略则索引全部}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Term data into OpenSearch';

    /** @var bool 是否为测试模式（只打印，不写入 OpenSearch） */
    private bool $isTest = false;

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected OpenSearchService $openSearchService,
        protected TermService $termService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * 遍历所有（或指定）DhammaTerm，逐条构建文档并写入 OpenSearch。
     * 测试模式下（--test）只打印文档内容，不执行写入。
     *
     * @return int  0 表示成功，1 表示失败
     */
    public function handle(): int
    {
        $word = $this->option('word');

        if ($this->option('test')) {
            $this->isTest = true;
            $this->info('test mode');
        }

        try {
            [$connected, $message] = $this->openSearchService->testConnection();
            if (!$connected) {
                $this->error($message);
                Log::error($message);
                return 1;
            }

            $total = DhammaTerm::count();
            $terms = DhammaTerm::select(['guid', 'word'])->orderBy('updated_at', 'asc');

            if ($word) {
                $terms = $terms->where('word', $word);
            }

            $overallStatus = 0;

            foreach ($terms->cursor() as $key => $term) {
                $percent = (int) (($key * 100) / $total);
                $this->info("[{$percent}%]-{$key}  " . $term->word);
                $this->indexTerm($term->guid);
            }

            return $overallStatus;
        } catch (\Exception $e) {
            $this->error('Failed to index Term data: ' . $e->getMessage());
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
     * @return void
     */
    protected function indexTerm(string $id): void
    {
        $termData    = $this->termService->find($id, 'text');
        $channelName = $termData['channel']['name'] ?? '';
        $isCommunity = $this->termService->isCommunity($termData['channel_id']);
        $content     = $termData['html'] ?? $termData['meaning'];

        $document = [
            'id'            => "term_{$id}",
            'resource_id'   => $id,
            'resource_type' => 'term',
            'title'         => [
                'text' => [
                    'pali' => $termData['word'],
                    'zh'   => $termData['meaning'],
                ],
                'suggest' => [
                    'pali' => [$termData['word']],
                    'zh'   => [$termData['meaning']],
                ],
            ],
            'summary' => [
                'text' => $termData['summary'] ?? $termData['note'] ?? '',
            ],
            'content'     => [],
            'bold_single' => [$termData['meaning'], $termData['word']],
            'related_id'  => $termData['word'],
            'category'    => [],
            'tags'        => $isCommunity ? ['community'] : [],
            'language'    => $termData['language'],
            'updated_at'  => now()->toIso8601String(),
            'path'        => $termData['studio']['realName'] . "/{$channelName}",
        ];

        // TODO: 补充语言判断，将内容放入对应的 text.pali 或 text.zh 字段
        $plainText = strip_tags($content);
        if (str_contains($termData['language'], 'zh')) {
            $document['content']['text']['zh'] = $plainText;
        } else {
            $document['content']['text']['zh'] = $plainText;
        }
        $document['content']['display']    = $content;             // 展示

        if ($this->isTest) {
            $this->info($document['title']['text']['pali']);
            $this->info($document['summary']['text']);
        } else {
            $this->openSearchService->create($document['id'], $document);
        }
    }
}
