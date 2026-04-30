<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;


use Illuminate\Http\Request;
use App\Models\ProgressChapter;
use App\Models\PaliText;
use App\Models\Sentence;

use App\Services\ChapterService;
use App\Services\PaliContentService;
use App\Services\OpenSearchService;

use App\DTO\Search\HitItemDTO;


class BookController extends Controller
{
    protected $maxChapterLen = 50000;
    protected $minChapterLen = 100;

    /**
     * 构造函数，注入 OpenSearchService
     *
     * @param  \App\Services\OpenSearchService  $searchService
     */
    public function __construct(protected OpenSearchService $searchService) {}

    public function show($id)
    {
        $bookRaw = $this->loadBook($id);

        if (!$bookRaw) {
            abort(404);
        }

        //查询章节
        $channelId = $bookRaw->channel_id; // 替换为具体的 channel_id 值

        $book = $this->getBookInfo($bookRaw);
        $book['contents'] = $this->getBookToc($bookRaw->book, $bookRaw->para, $channelId);

        // 获取其他版本
        $others = ProgressChapter::with('channel.owner')
            ->where('book', $bookRaw->book)
            ->where('para', $bookRaw->para)
            ->whereHas('channel', function ($query) {
                $query->where('status', 30);
            })
            ->where('progress', '>', 0.2)
            ->get();

        $otherVersions = [];
        $others->each(function ($book) use (&$otherVersions, $id) {
            $otherVersions[] = $this->getBookInfo($book);
        });

        return view('library.tipitaka.show', compact('book', 'otherVersions'));
    }


    public function read(Request $request, $id)
    {
        $start = microtime(true);
        $lap = function (string $label) use ($start): void {
            $elapsed = round((microtime(true) - $start) * 1000, 2);
            Log::debug("[book.read] {$label}", ['elapsed_ms' => $elapsed]);
        };

        $lap('start');

        $channelId = $request->input('channel');
        $openSearchId = "tipitaka_chapter_{$id}_{$channelId}";

        $chapter = HitItemDTO::fromArray($this->searchService->get($openSearchId))->toArray();
        $lap('searchService->get + HitItemDTO');

        [$bookId, $paraId] = explode('-', $id);

        $chapterService = app(ChapterService::class);

        $book = [];

        $book['toc'] = $this->getBookToc((int)$bookId, (int)$paraId, $channelId, 2, 7);
        $lap('getBookToc');

        $book['categories'] = $chapter['category'];
        $book['title']      = $chapter['title'];
        $book['author']     = 'author'; // FIXME
        $book['tags']       = [];

        $book['pagination'] = $this->pagination((int)$bookId, (int)$paraId, $channelId);
        $lap('pagination');

        $book['content'] = $chapter['display'];

        $channels = $chapterService->publicChannels((int)$bookId, (int)$paraId);
        $lap('publicChannels');

        $editor_link = config('mint.server.dashboard_base_path')
            . "/workspace/tipitaka/chapter/{$id}?channel={$channelId}";

        $view = view('library.book.read', compact('book', 'channels', 'editor_link'));
        $lap('view compiled — total');

        return $view;
    }

    private function loadBook($id)
    {
        $book = ProgressChapter::with('channel.owner')->find($id);
        return $book;
    }

    public function toggleTheme(Request $request)
    {
        $theme = $request->input('theme', 'light');
        session(['theme' => $theme]);
        return response()->json(['status' => 'success']);
    }

    private function getBookInfo($book)
    {
        $title = $book->title;
        if (empty($title)) {
            $title = PaliText::where('book', $book->book)
                ->where('paragraph', $book->para)->first()->toc;
        }
        return [
            "id" => $book->uid,
            "title" => $title,
            "author" => $book->channel->name,
            "publisher" => $book->channel->owner,
            "type" => __('label.' . $book->channel->type),
            "category_id" => 11,
            "cover" => "/assets/images/cover/1/214.jpg",
            "description" => $book->summary ?? "",
            "language" => __('language.' . $book->channel->lang),
        ];
    }

    private function getBookToc(int $book, int $paragraph, string $channelId, $minLevel = 2, $maxLevel = 2): array
    {
        $currBook = $this->bookStart($book, $paragraph);

        $start = $currBook->paragraph;
        $end   = $currBook->paragraph + $currBook->chapter_len - 1;

        $paliTexts = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$start, $end])
            ->whereBetween('level', [$minLevel, $maxLevel])
            ->orderBy('paragraph')
            ->get();

        $chapters = ProgressChapter::where('book', $book)
            ->whereBetween('para', [$start, $end])
            ->where('channel_id', $channelId)
            ->orderBy('para')
            ->get();

        // keyBy 建索引，map 里 O(1) 查找，完全避免 toArray() 序列化和 array_filter O(n×m) 扫描
        $chaptersIndexed = $chapters->keyBy('para');

        return $paliTexts->map(function ($paliText) use ($chaptersIndexed, $channelId) {
            $title    = $paliText->toc;
            $summary  = '';
            $progress = 0;
            $disabled = true;

            /** @var ProgressChapter|null $chapter */
            $chapter = $chaptersIndexed->get($paliText->paragraph);
            if ($chapter) {
                if (!empty($chapter->title))   $title   = $chapter->title;
                if (!empty($chapter->summary)) $summary = $chapter->summary;
                $progress = (int)($chapter->progress * 100);
                $disabled = false;
            }

            return [
                'id'       => "{$paliText->book}-{$paliText->paragraph}",
                'channel'  => $channelId,
                'title'    => $title,
                'summary'  => $summary,
                'progress' => $progress,
                'level'    => (int)$paliText->level,
                'disabled' => $disabled,
            ];
        })->all();
    }

    public function getBookCategory($book, $paragraph)
    {
        $tags = PaliText::with('tagMaps.tags')
            ->where('book', $book)
            ->where('paragraph', $paragraph)
            ->first()->tagMaps->map(function ($tagMap) {
                return $tagMap->tags;
            })->toArray();
        return $tags;
    }

    private function bookStart($book, $paragraph)
    {
        $currBook = PaliText::where('book', $book)
            ->where('paragraph', '<=', $paragraph)
            ->where('level', 1)
            ->orderBy('paragraph', 'desc')
            ->first();
        return $currBook;
    }


    public function pagination(int $book, int $para, string $channelId)
    {
        $currBook = $this->bookStart($book, $para);
        $start = $currBook->paragraph;
        $end = $currBook->paragraph + $currBook->chapter_len - 1;
        // 查询起始段落
        $paragraphs = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$start, $end])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->get();
        $curr = $paragraphs->firstWhere('paragraph', $para);
        $current = $curr; //实际显示的段落
        $endParagraph = $curr->paragraph + $curr->chapter_len - 1;
        if ($curr->chapter_strlen > $this->maxChapterLen) {
            //太大了，修改结束位置 找到下一级
            foreach ($paragraphs as $key => $paragraph) {
                if ($paragraph->paragraph > $curr->paragraph) {
                    if ($paragraph->chapter_strlen <= $this->maxChapterLen) {
                        $endParagraph = $paragraph->paragraph + $paragraph->chapter_len - 1;
                        $current = $paragraph;
                        break;
                    }
                    if ($paragraph->level <= $curr->level) {
                        //不能往下走了，就是它了
                        $endParagraph = $paragraphs[$key - 1]->paragraph + $paragraphs[$key - 1]->chapter_len - 1;
                        $current = $paragraph;
                        break;
                    }
                }
            }
        }
        $start = $curr->paragraph;
        $end = $endParagraph;
        $nextPali = $this->next($current->book, $current->paragraph, $current->level);
        $prevPali = $this->prev($current->book, $current->paragraph, $current->level);

        $next = null;
        if ($nextPali) {
            $nextTranslation = ProgressChapter::with('channel.owner')
                ->where('book', $nextPali->book)
                ->where('para', $nextPali->paragraph)
                ->where('channel_id', $channelId)
                ->first();
            if ($nextTranslation) {
                if (!empty($nextTranslation->title)) {
                    $next['title'] = $nextTranslation->title;
                } else {
                    $next['title'] = $nextPali->toc;
                }
                $next['id'] = "{$nextPali->book}-{$nextPali->paragraph}";
            }
        }

        $prev = null;
        if ($prevPali) {
            $prevTranslation = ProgressChapter::with('channel.owner')
                ->where('book', $prevPali->book)
                ->where('para', $prevPali->paragraph)
                ->where('channel_id', $channelId)
                ->first();
            if ($prevTranslation) {
                if (!empty($prevTranslation->title)) {
                    $prev['title'] = $prevTranslation->title;
                } else {
                    $prev['title'] = $prevPali->toc;
                }
                $prev['id'] = "{$prevPali->book}-{$prevPali->paragraph}";
            }
        }

        return compact('start', 'end', 'next', 'prev');
    }

    public function next($book, $paragraph, $level)
    {
        $next = PaliText::where('book', $book)
            ->where('paragraph', '>', $paragraph)
            ->where('level', $level)
            ->orderBy('paragraph')
            ->first();
        return $next ?? null;
    }
    public function prev($book, $paragraph, $level)
    {
        $prev = PaliText::where('book', $book)
            ->where('paragraph', '<', $paragraph)
            ->where('level', $level)
            ->orderBy('paragraph', 'desc')
            ->first();

        return $prev ?? null;
    }
    public function show2($id)
    {
        // Sample book data (replace with database query)
        $book = [
            'title' => 'Sample Book Title',
            'author' => 'John Doe',
            'category' => 'Fiction',
            'tags' => ['Adventure', 'Mystery', 'Bestseller'],
            'toc' => ['Introduction', 'Chapter 1', 'Chapter 2', 'Conclusion'],
            'content' => [
                'This is the introduction to the book...',
                'Chapter 1 content goes here...',
                'Chapter 2 content goes here...',
                'Conclusion of the book...',
            ],
            'downloads' => [
                ['format' => 'PDF', 'url' => '#'],
                ['format' => 'EPUB', 'url' => '#'],
                ['format' => 'MOBI', 'url' => '#'],
            ],
        ];

        // Sample related books (replace with database query)
        $relatedBooks = [
            [
                'title' => 'Related Book 1',
                'description' => 'A thrilling adventure...',
                'image' => 'https://via.placeholder.com/300x200',
                'link' => '#',
            ],
            [
                'title' => 'Related Book 2',
                'description' => 'A mystery novel...',
                'image' => 'https://via.placeholder.com/300x200',
                'link' => '#',
            ],
            [
                'title' => 'Related Book 3',
                'description' => 'A bestseller...',
                'image' => 'https://via.placeholder.com/300x200',
                'link' => '#',
            ],
        ];

        return view('library.book.read2', compact('book', 'relatedBooks'));
    }
}
