<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchPaliDataService;
use App\Services\OpenSearchService;
use App\Services\SummaryService;
use App\Services\TagService;
use Illuminate\Support\Facades\Log;
use App\Models\PaliText;

class IndexTipitaka extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan opensearch:index-pali 93 --para=6
     * @var string
     */
    protected $signature = 'opensearch:index-tipitaka {book : The book ID to index data for}
    {--test}
    {--para= : index paragraph No. omit to all}
    {--summary=on}
    {--resume}
    {--granularity= : The granularity to index (paragraph, sutta, sentence; omit to index all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Pali data into OpenSearch for a specified book and optional granularity (all granularities if not specified)';

    protected $searchPaliDataService;
    protected $openSearchService;
    protected $summaryService;
    protected $tagService;
    private $isTest = false;
    private $summary = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        SearchPaliDataService $searchPaliDataService,
        OpenSearchService $openSearchService,
        SummaryService $summaryService,
        TagService $tagService
    ) {
        parent::__construct();
        $this->searchPaliDataService = $searchPaliDataService;
        $this->openSearchService = $openSearchService;
        $this->summaryService = $summaryService;
        $this->tagService = $tagService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $book = (int)$this->argument('book');
        $granularity = $this->option('granularity');
        $paragraph = $this->option('para');
        $this->summary = $this->option('summary') === 'on';

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
            $maxBookId = PaliText::max('book');
            if ($book === 0) {
                $booksId = range(1, $maxBookId);
            } else if ($this->option('resume')) {
                $booksId = range($book, $maxBookId);
            } else {
                $booksId = [$book];
            }
            foreach ($booksId as $key => $bookId) {
                $this->indexTipitakaBook($bookId, $paragraph);
            }

            return $overallStatus;
        } catch (\Exception $e) {
            $this->error("Failed to index Pali data: " . $e->getMessage());
            Log::error("Failed to index Pali data for book: $book, granularity: " . ($granularity ?: 'all'), ['error' => $e]);
            return 1;
        }
    }

    /**
     * Index Pali paragraphs for a given book.
     *
     * @param int $book
     * @return int
     */
    protected function indexTipitakaBook($book, $paragraph = null)
    {
        $this->info("Starting to index paragraphs for book: $book");
        $total = 0;
        if ($paragraph) {
            $paragraphs = PaliText::where('book', $book)
                ->where('paragraph', $paragraph)
                ->orderBy('paragraph')->cursor();
        } else {
            $paragraphs = PaliText::where('book', $book)
                ->orderBy('paragraph')->cursor();
        }
        $bookUid = PaliText::where('book', $book)->where('level', 1)->first()->uid;
        $category = $this->tagService->getTagsName($bookUid);
        $headings = [];
        $currChapterTitle = '';
        $commentaryId = '';
        $currSession = [];
        foreach ($paragraphs as $key => $para) {
            $total++;
            if ($para->level < 8) {
                $currChapterTitle = $para->toc;
            }
            if ($para->class === 'nikaya') {
                $nikaya = $para->text;
            }
            $paraContent = $this->searchPaliDataService
                ->getParaContent($para['book'], $para['paragraph']);
            if (!empty($commentaryId)) {
                $currSession[] = $paraContent;
            }
            if (isset($paraContent['commentary'])) {
                if (!empty($commentaryId)) {
                    //保存 session
                    $this->indexPaliSession($para->toArray(), $currSession, $currChapterTitle, $commentaryId);
                    $currSession = [];
                }
                $commentaryId = $paraContent['commentary'];
            }
            $this->indexParagraph($para->toArray(), $paraContent, $commentaryId, $category);
            $this->info("{$para['book']}-[{$para['paragraph']}]-[{$commentaryId}]");
            usleep(10000);
        }

        $this->info("Successfully indexed $total paragraphs for book: $book");
        Log::info("Indexed $total paragraphs for book: $book");

        return 0;
    }
    /**
     *
     */
    protected function indexParagraph($paraInfo, $paraContent, $related_id, array $category)
    {
        $paraId = $paraInfo['book'] . '-' . $paraInfo['paragraph'];
        $resource_id = $paraInfo['uid'];
        $path = json_decode($paraInfo['path']);
        if (is_array($path) && count($path) > 0) {
            $title = end($path)->title;
        } else {
            $title = '';
        }
        $document = [
            'id' => "tipitaka_paragraph_pi_{$paraId}",
            'resource_id' => $resource_id, // Use uid from getPaliData for resource_id
            'resource_type' => 'tipitaka',
            'title' => [
                'pali' => $title,
            ],
            'summary' => [
                'text' => $this->summary  ? $this->summaryService->summarize($paraContent['markdown']) : ''
            ],
            'content' => [
                'pali' => $paraContent['text'],
                'suggest' => $paraContent['words'],
            ],
            'bold_single' => implode(' ', $paraContent['bold1']),
            'bold_multi' => implode(' ', array_merge($paraContent['bold2'], $paraContent['bold3'])),
            'related_id' => $paraId,
            'category' => $category, // Assuming Pali paragraphs are sutta; adjust as needed
            'language' => 'pi',
            'updated_at' => now()->toIso8601String(),
            'granularity' => 'paragraph',
            'path' => $this->getPathTitle($path),
        ];
        if ($paraInfo['level'] < 8) {
            $document['title']['suggest'] = $paraContent['words'];
        }
        if ($this->isTest) {
            $this->info($document['title']['pali']);
            $this->info($document['summary']['text']);
        } else {
            $this->openSearchService->create($document['id'], $document);
        }
        return;
    }

    /**
     *
     */
    protected function indexPaliSession($paraInfo, $contents, $currChapter, $related_id)
    {
        $markdown = [];
        $text = [];
        $bold_single = [];
        $bold_multi = [];
        foreach ($contents as $key => $content) {
            $markdown[] = $content['markdown'];
            $text[] = $content['text'];
            $bold_single = array_merge($bold_single, $content['bold1']);
            $bold_multi = array_merge($bold_multi, $content['bold2'], $content['bold3']);
        }
        $document = [
            'id' => "pali_session_{$related_id}",
            'resource_id' => $paraInfo['uid'], // Use uid from getPaliData for resource_id
            'resource_type' => 'original_text',
            'title' => [
                'pali' => "{$currChapter} paragraph {$paraInfo['paragraph']}"
            ],
            'summary' => [
                'text' => $this->summary ? $this->summaryService->summarize($content['markdown']) : ''
            ],
            'content' => [
                'pali' => implode("\n\n", $markdown),
            ],
            'bold_single' => implode(" ", $bold_single),
            'bold_multi' => implode(" ", $bold_multi),
            'related_id' => $related_id,
            'category' => 'pali', // Assuming Pali paragraphs are sutta; adjust as needed
            'language' => 'pali',
            'updated_at' => now()->toIso8601String(),
            'granularity' => 'session',
            'path' => $this->getPathTitle(json_decode($paraInfo['path'])),
        ];
        if ($this->isTest) {
            $this->info($document['title']['pali']);
            $this->info($document['summary']['text']);
        } else {
            $this->openSearchService->create($document['id'], $document);
        }
        return;
    }



    /**
     * Index Pali suttas for a given book (placeholder for future implementation).
     *
     * @param int $book
     * @return int
     */
    protected function indexPaliSutta($book)
    {
        $this->warn("Sutta indexing is not yet implemented for book: $book");
        Log::warning("Sutta indexing not implemented for book: $book");
        return 1;
    }

    /**
     * Index Pali sentences for a given book (placeholder for future implementation).
     *
     * @param int $book
     * @return int
     */
    protected function indexPaliSentences($book)
    {
        $this->warn("Sentence indexing is not yet implemented for book: $book");
        Log::warning("Sentence indexing not implemented for book: $book");
        return 1;
    }


    private function getPathTitle(array $input)
    {
        $output = [];
        foreach ($input as $key => $node) {
            $output[] = $node->title;
        }
        return implode('/', $output);
    }
}
