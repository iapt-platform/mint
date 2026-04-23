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
     * php artisan opensearch:index-term --word=anomadassī
     * @var string
     */
    protected $signature = 'opensearch:index-term
    {--test}
    {--word= : index word. omit to all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Term data into OpenSearch ';

    private $isTest = false;
    private $summary = false;

    /**
     * Create a new command instance.
     *
     * @return void
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
     * @return int
     */
    public function handle()
    {
        $word = $this->option('word');


        if ($this->option('test')) {
            $this->isTest = true;
            $this->info('test mode');
        }


        try {
            // Test OpenSearch connection
            [$connected, $message] = $this->openSearchService->testConnection();
            if (!$connected) {
                $this->error($message);
                Log::error($message);
                return 1;
            }
            $overallStatus = 0; // Track overall command status (0 for success, 1 for any failure)
            $total = DhammaTerm::count();
            $terms = DhammaTerm::select(['guid', 'word'])->orderBy('updated_at', 'asc');
            if ($word) {
                $terms = $terms->where('word', $word);
            }

            foreach ($terms->cursor() as $key => $term) {
                $percent = (int)(($key * 100) / $total);
                $this->info("[{$percent}%]-{$key}  " . $term->word);
                $this->indexTerm($term->guid);
            }

            return $overallStatus;
        } catch (\Exception $e) {
            $this->error("Failed to index Pali data: " . $e->getMessage());
            Log::error("Failed to index Term data : ", ['error' => $e]);
            return 1;
        }
    }

    /**
     *
     */
    protected function indexTerm(string $id)
    {
        $termData = $this->termService->find($id, 'text');
        $channelName = $termData["channel"]['name'] ?? '';
        $isCommunity = $this->termService->isCommunity($termData["channel_id"]);
        $content = $termData['html'] ?? $termData['meaning'];
        $document = [
            'id' => "term_{$id}",
            'resource_id' => $id, // Use uid from getPaliData for resource_id
            'resource_type' => 'term',
            'title' => [
                'pali' => $termData['word'],
                'zh' => $termData['meaning'],
                'suggest_pali' => [$termData['word']],
                'suggest_zh' => [$termData['meaning']],
            ],
            'summary' => [
                'text' => $termData['summary'] ?? $termData['note'] ?? ''
            ],
            'content' => [],
            'bold_single' => [$termData['meaning'], $termData['word']],
            'related_id' => $termData['word'],
            'category' => [],
            'tags' => $isCommunity ? ['community'] : [],
            'language' => $termData['language'],
            'updated_at' => now()->toIso8601String(),
            'path' => $termData['studio']['realName'] . "/{$channelName}",
        ];

        if (strpos($termData['language'], 'zh') !== false) {
            $document['content']['zh'] = $content;
        } else {
            //TODO 判断语言 放在合适的字段
            $document['content']['zh'] = $content;
        }

        if ($this->isTest) {
            $this->info($document['title']['pali']);
            $this->info($document['summary']['text']);
        } else {
            $this->openSearchService->create($document['id'], $document);
        }
        return;
    }
}
