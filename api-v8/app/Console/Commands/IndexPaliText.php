<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchPaliDataService;
use App\Services\OpenSearchService;
use Illuminate\Support\Facades\Log;

class IndexPaliText extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan opensearch:index-pali 93
     * @var string
     */
    protected $signature = 'opensearch:index-pali {book : The book ID to index data for} {--granularity= : The granularity to index (paragraph, sutta, sentence; omit to index all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Pali data into OpenSearch for a specified book and optional granularity (all granularities if not specified)';

    protected $searchPaliDataService;
    protected $openSearchService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SearchPaliDataService $searchPaliDataService, OpenSearchService $openSearchService)
    {
        parent::__construct();
        $this->searchPaliDataService = $searchPaliDataService;
        $this->openSearchService = $openSearchService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $book = $this->argument('book');
        $granularity = $this->option('granularity');

        try {
            // Test OpenSearch connection
            [$connected, $message] = $this->openSearchService->testConnection();
            if (!$connected) {
                $this->error($message);
                Log::error($message);
                return 1;
            }

            // Define all possible granularities
            $granularities = ['paragraph', 'sutta', 'sentence'];

            // If granularity is not set, index all granularities; otherwise, index the specified one
            $targetGranularities = empty($granularity) ? $granularities : [$granularity];

            $overallStatus = 0; // Track overall command status (0 for success, 1 for any failure)

            foreach ($targetGranularities as $gran) {
                // Validate granularity
                if (!in_array($gran, $granularities)) {
                    $this->error("Invalid granularity: $gran. Supported values: " . implode(', ', $granularities));
                    Log::error("Invalid granularity provided: $gran");
                    $overallStatus = 1;
                    continue;
                }

                // Route to appropriate indexing method
                switch ($gran) {
                    case 'paragraph':
                        $status = $this->indexPaliParagraphs($book);
                        break;
                    case 'sutta':
                        $status = $this->indexPaliSutta($book);
                        break;
                    case 'sentence':
                        $status = $this->indexPaliSentences($book);
                        break;
                    default:
                        $status = 1; // Should not reach here due to validation
                }

                // Update overall status if any indexing fails
                $overallStatus = max($overallStatus, $status);
            }

            if ($overallStatus === 0) {
                $this->info("Successfully completed indexing for book: $book");
            } else {
                $this->warn("Indexing completed with errors for book: $book");
            }

            return $overallStatus;
        } catch (\Exception $e) {
            $this->error("Failed to index Pali data: " . $e->getMessage());
            Log::error("Failed to index Pali data for book: $book, granularity: " . ($granularity ?: 'all'), ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Index Pali paragraphs for a given book.
     *
     * @param int $book
     * @return int
     */
    protected function indexPaliParagraphs($book)
    {
        $this->info("Starting to index paragraphs for book: $book");

        // Fetch all paragraphs for the book
        $result = $this->searchPaliDataService->getPaliData($book, 1, null);
        $paragraphs = $result['rows'];
        $total = count($paragraphs);

        if ($total === 0) {
            $this->warn("No paragraphs found for book: $book");
            return 0;
        }

        $this->info("Found $total paragraphs to index");

        // Create progress bar
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($paragraphs as $paragraph) {
            // Map paragraph data to OpenSearch document structure
            $document = [
                'id' => "pali_para_{$book}_{$paragraph['paragraph']}",
                'resource_id' => $paragraph['uid'], // Use uid from getPaliData for resource_id
                'resource_type' => 'paragraph',
                'title' => [
                    'display' => "Paragraph {$paragraph['paragraph']} of Book {$book}"
                ],
                'summary' => [
                    'text' => $paragraph['text']
                ],
                'content' => [
                    'display' => $paragraph['markdown'],
                    'text' => $paragraph['text'], // Remove markdown for plain text
                    'exact' => $paragraph['text'],
                ],
                'bold_single' => $paragraph['bold1'],
                'bold_multi' => $paragraph['bold2'] . ' ' . $paragraph['bold3'],
                'related_id' => $paragraph['pcd_book_id'],
                'category' => 'pali', // Assuming Pali paragraphs are sutta; adjust as needed
                'language' => 'pali',
                'updated_at' => now()->toIso8601String(),
                'granularity' => 'paragraph',
            ];

            // Index the document in OpenSearch
            $this->openSearchService->create($document['id'], $document);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully indexed $total paragraphs for book: $book");
        Log::info("Indexed $total paragraphs for book: $book");

        return 0;
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
}
