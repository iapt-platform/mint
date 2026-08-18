<?php

namespace App\Console\Commands;

use App\Models\PaliText;
use App\Services\SummaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpgradeSystemSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:sys.summary {book : The book ID to index data for} {--para= : index paragraph No. omit to all} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $summaryService;

    private $isTest = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SummaryService $summaryService)
    {
        parent::__construct();
        $this->summaryService = $summaryService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $book = $this->argument('book');
        $paragraph = $this->option('para');

        if ($this->option('test')) {
            $this->isTest = true;
            $this->info('test mode');
        }

        if ((int) $book === 0) {
            $maxBookId = PaliText::max('book');
            $booksId = range(1, $maxBookId);
        } else {
            $booksId = [$book];
        }
        foreach ($booksId as $key => $bookId) {
            $this->summarize($bookId, $paragraph);
        }

        return 0;
    }

    /**
     * Index Pali paragraphs for a given book.
     *
     * @param  int  $book
     * @return int
     */
    protected function summarize($book, $paragraph = null)
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

        $commentaryId = '';
        $chapterContent = [];
        $chapterStart = 0;
        $chapterEnd = 0;
        foreach ($paragraphs as $key => $para) {
            $total++;
            $content = $para->text;
            if ($para->level < 8) {
                $start = $para->paragraph;
                $end = $start + $para->chapter_len - 1;
                $subChapter = PaliText::whereBetween('paragraph', [$start, $end])
                    ->where('level', '<', 8)
                    ->count();
                if ($subChapter === 0) {
                    $chapterStart = $start;
                    $chapterEnd = $end;
                    $chapterContent = [];
                } else {
                    $chapterStart = 0;
                    $chapterEnd = 0;
                }
            } else {
                $summary = $this->summaryService->summarize($para->text);
                $this->info("{$para['book']}-[{$para['paragraph']}]-{$summary}");
                if ($chapterStart !== 0) {
                    $chapterContent[] = $summary;
                }
                if ($para->paragraph === $chapterEnd) {
                    $chapterSummary = $this->summaryService->summarize(implode(' ', $chapterContent));
                    $this->info("{$para['book']}-[{$chapterStart}]-{$chapterSummary}");
                    $chapterStart = 0;
                    $chapterEnd = 0;
                    $chapterContent = [];
                }
            }
        }

        $this->info("Successfully indexed $total paragraphs for book: $book");
        Log::info("Indexed $total paragraphs for book: $book");

        return 0;
    }
}
