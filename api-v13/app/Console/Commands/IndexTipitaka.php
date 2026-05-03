<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchPaliDataService;
use App\Services\OpenSearchService;
use App\Services\SummaryService;
use App\Services\TagService;
use Illuminate\Support\Facades\Log;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\PaliContentService;
use App\Http\Api\ChannelApi;
use App\Models\ProgressChapter;

class IndexTipitaka extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan opensearch:index-tipitaka 93 --para=6 --granularity=chapter
     * @var string
     */
    protected $signature = 'opensearch:index-tipitaka {book : The book ID to index data for}
    {--test}
    {--para= : index paragraph No. omit to all}
    {--summary=on}
    {--resume}
    {--granularity=all : The granularity to index (paragraph, sutta, sentence; omit to index all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Pali data into OpenSearch for a specified book and optional granularity (all granularities if not specified)';


    private $isTest = false;
    private $summary = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected SearchPaliDataService $searchPaliDataService,
        protected OpenSearchService $openSearchService,
        protected SummaryService $summaryService,
        protected TagService $tagService
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
                if (
                    $this->option('granularity') === 'chapter' ||
                    $this->option('granularity') === 'all'
                ) {
                    $this->indexChapter($bookId);
                }
                if (
                    $this->option('granularity') === 'paragraph' ||
                    $this->option('granularity') === 'all'
                ) {
                    $this->indexTipitakaParagraph($bookId, $paragraph);
                }
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
    protected function indexTipitakaParagraph($book, $paragraph = null)
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
                'text' => ['pali' => $title,],
            ],
            'summary' => [
                'text' => $this->summary  ? $this->summaryService->summarize($paraContent['markdown']) : ''
            ],
            'content' => [
                'text' => ['pali' => $paraContent['text']],
                'suggest' => ['pali' => $paraContent['words']],
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
            $document['title']['suggest']['pali'] = $paraContent['words'];
        }
        if ($this->isTest) {
            $this->info($document['title']['text']['pali']);
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
    protected function indexChapter($book)
    {
        $this->info("Starting to index paragraphs for book: $book");
        $total = 0;
        $chapters = PaliText::where('book', $book)
            ->where('level', '<', 8)
            ->orderBy('paragraph')->get();
        foreach ($chapters as $key => $chapter) {
            if ($chapter->level === 1) {
                $category = $this->tagService->getTagsName($chapter->uid);
            }
            /**
             * 章节的起始位置算法
             * 从章节的标题，到下一个章节的标题之间
             */
            $start = $chapter->paragraph;
            if ($key === count($chapters) - 1) {
                $end = PaliText::where('book', $book)
                    ->orderBy('paragraph', 'desc')->first()
                    ->value('paragraph');
            } else {
                $end = $chapters[$key + 1]->paragraph;
            }
            //获取这个段落之间的全部channel
            $channels = Sentence::where('book_id', $book)
                ->whereBetween('paragraph', [$start, $end])
                ->select('channel_uid')
                ->groupBy('channel_uid')->get();
            $this->info("index chapter start={$start} end={$end}");

            foreach ($channels as $channel) {
                $display = [];
                $content = [];
                $channelInfo = ChannelApi::getById($channel->channel_uid);
                if (!$channelInfo) {
                    Log::error('invalid channel', ['id' => $channel->channel_uid]);
                    continue;
                }
                $this->info('channel =' . $channelInfo['name']);
                if ($channelInfo['type'] === 'wbw') {
                    $this->info('wbw channel skip');
                    continue;
                }
                $paragraphsData = app(PaliContentService::class)->paragraphs(
                    $book,
                    $start,
                    $end,
                    [$channel->channel_uid],
                    ['mode' => 'read', 'format' => 'html', 'original' => false]
                );
                //生成html数据

                $title = '';
                foreach ($paragraphsData as $paragraph) {
                    $translation = [];
                    $original = [];
                    foreach ($paragraph['children'] as  $sent) {
                        if (isset($sent['translation'])) {
                            foreach ($sent['translation'] as  $tran) {
                                if ($tran['channel']['id'] === $channel->channel_uid) {
                                    $html = $tran['html'] ?? $tran['content'];
                                    $translation[] = "<span class='sentence'>{$html}</span>";
                                    if ($tran['para'] === $start && !empty($curr)) {
                                        $title = $curr;
                                    }
                                }
                            }
                        }
                        if (
                            isset($sent['origin']) ||
                            is_array($sent['origin']) ||
                            count($sent['origin']) > 0
                        ) {
                            foreach ($sent['origin'] as  $origin) {
                                if ($origin['channel']['id'] === $channel->channel_uid) {
                                    $html = $origin['html'] ?? $origin['content'];
                                    $original[] = "<span class='sentence origin'>{$html}</span>";
                                    if (empty($title) && $origin['para'] === $start && !empty($curr)) {
                                        $title = $curr;
                                    }
                                }
                            }
                        }
                    }

                    $level = $paragraph['para'] === $start ? $chapter->level : 0;
                    $strOriginal = implode('', $original);
                    $strTranslation = implode('', $translation);

                    if ($channelInfo['type'] === 'original') {
                        $htmlContent = $strOriginal;
                    } else {
                        $htmlContent = $strTranslation;
                    }

                    $area = $channelInfo['type'] === 'original' ? 'original' : 'translation';

                    if ($level > 0) {
                        $display[] = "<div class='{$area}' data-para='{$paragraph['para']}'><h{$level}>{$htmlContent}</h{$level}></div>";
                    } else {
                        $display[] = "<div class='{$area}' data-para='{$paragraph['para']}'><p>{$htmlContent}</p></div>";
                    }
                }
                $this->chapterSave([
                    'book' => $book,
                    'para' => $start,
                    'level' => $chapter->level,
                    'channel' => $channel->channel_uid,
                    'content' => implode('', $display),
                    'title' => strip_tags($title),
                    'cat' => $category ?? null
                ]);
            }
        }


        return 0;
    }

    protected function chapterSave(array $param)
    {
        $progress = ProgressChapter::where('book', $param['book'])
            ->where('para', $param['para'])
            ->where('channel_id', $param['channel'])
            ->first();
        $channel = ChannelApi::getById($param['channel']);
        $document = [
            'id'            => "tipitaka_chapter_{$param['book']}-{$param['para']}_{$param['channel']}",
            'resource_id'   => $progress ? $progress->uid : "{$param['book']}-{$param['para']}_{$param['channel']}",
            'resource_type' => 'tipitaka',
            'title'         => [],
            'summary' => [
                'text' => '',
            ],
            'content'     => [],
            'related_id'  => "{$param['book']}-{$param['para']}",
            'category'    => $param['cat'],
            'language'    => $channel['lang'],
            'updated_at'  => now()->toIso8601String(),
            'granularity' => $param['level'] === 1 ? 'book' : 'chapter',
        ];

        // TODO: 补充语言判断，将内容放入对应的 text.pali 或 text.zh 字段
        $plainText = strip_tags($param['content']);
        $title = strip_tags($param['title']);
        if (str_contains($channel['lang'], 'zh')) {
            $document['content']['text']['zh'] = $plainText;
            $document['title']['text']['zh'] = $title;
        } else {
            $document['content']['text']['pali'] = $plainText;
            $document['title']['text']['pali'] = $title;
        }
        $document['content']['display']    = $param['content'];             // 展示

        if ($this->isTest) {
            $this->info($param['content']);
        } else {
            $this->openSearchService->create($document['id'], $document);
            $this->info("create index {$document['id']} size=" . strlen($param['content']));
        }
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
