<?php

namespace App\Http\Controllers\Library;

use App\DTO\Search\HitItemDTO;
use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;
use App\Http\Controllers\Controller;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Sentence;
use App\Services\ChapterService;
use App\Services\OpenSearchService;
use App\Services\PaliTextService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BookController extends Controller
{
    /**
     * chapter 聚合显示的字符数阈值。
     *
     * ES 中每个 chapter 节点只保存「本节点 → 下一节点」之间的正文，选中含子
     * chapter 的父节点时其自身文档往往只有标题。显示时按本阈值决定聚合粒度：
     * chapter_strlen 小于该值的节点，连同其覆盖区间内的所有子 chapter 一并展示；
     * 超过该值则向下钻取第一个不超过阈值的子 chapter。
     */
    protected $maxChapterStrlen = 30000;

    protected $minChapterLen = 100;

    /**
     * 构造函数，注入 OpenSearchService
     */
    public function __construct(
        protected OpenSearchService $searchService,
        protected PaliTextService $paliTextService
    ) {}

    public function show(string $id)
    {
        $bookRaw = $this->loadBook($id);

        if (! $bookRaw) {
            abort(404);
        }

        // 查询章节
        $channelId = $bookRaw->channel_id; // 替换为具体的 channel_id 值

        $book = $this->getBookInfo($bookRaw);
        $book['contents'] = $this->getBookToc($bookRaw->book, $bookRaw->para, $channelId);
        $book['book_title'] = $this->getBookTitle($bookRaw->book, $bookRaw->para, $channelId);
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
        $others->each(function ($book) use (&$otherVersions) {
            $otherVersions[] = $this->getBookInfo($book);
        });

        return view('library.tipitaka.show', compact('book', 'otherVersions'));
    }

    private function fetchCommentary(int $book, int $paraStart, int $paraEnd, string $channelId)
    {
        $notes = Sentence::where('book_id', $book)
            ->whereBetween('paragraph', [$paraStart, $paraEnd])
            ->where('channel_uid', $channelId)
            ->select(['uid', 'book_id', 'paragraph', 'word_start', 'word_end'])->get()->toArray();

        return $notes;
    }

    private function injectNoteMarkers(string $html, array $notesMap): string
    {
        if (empty($notesMap)) {
            return $html;
        }

        return preg_replace_callback(
            '/(<div class=\'sentence\' data-sid=\'([^\']+)\')/',
            function ($matches) use ($notesMap) {
                $sid = $matches[2];
                if (! isset($notesMap[$sid])) {
                    return $matches[0];
                }
                $uid = $notesMap[$sid];

                return $matches[1]." data-note-id='{$uid}'";
            },
            $html
        );
    }

    public function read(Request $request, string $id)
    {

        $channelId = $request->input('channel');

        [$bookId, $paraId] = explode('-', $id);
        $bookId = (int) $bookId;
        $paraId = (int) $paraId;

        // 解析选中 chapter 的实际显示区间，并聚合区间内全部 chapter 节点的 ES 内容，
        // 避免选中父 chapter 时仅显示一个标题。
        $range = $this->resolveDisplayRange($bookId, $paraId);
        $chapter = $this->fetchRangeContent($bookId, $channelId, $range['chapters'], $id);

        if ($request->has('comm')) {
            // 注释范围与显示区间保持一致（聚合后可能跨多个段落）
            $commentaries = $this->fetchCommentary($bookId, $range['start'], $range['end'], $request->input('comm'));
            // sid 格式：{book_id}-{paragraph}-{word_start}-{word_end}
            $notesMap = collect($commentaries)->keyBy(function ($note) {
                return "{$note['book_id']}-{$note['paragraph']}-{$note['word_start']}-{$note['word_end']}";
            })->map(fn ($note) => $note['uid'])->toArray();
        }

        $chapterService = app(ChapterService::class);

        $book = [];

        $book['toc'] = $this->getBookToc($bookId, $paraId, $channelId, 2, 7, [$range['start'], $range['end']]);
        $channel = ChannelApi::getById($channelId);
        $studio = StudioApi::getById($channel['studio_id']);
        $book['categories'] = $chapter['category'];
        $book['title'] = $chapter['title'];
        $book['author'] = $channel['name'];
        $book['studio'] = $studio;
        $book['tags'] = [];
        $book['book_title'] = $this->getBookTitle($bookId, $paraId, $channelId);

        $book['pagination'] = $this->pagination($bookId, $paraId, $channelId);

        if (isset($notesMap)) {
            $book['content'] = $this->injectNoteMarkers($chapter['display'], $notesMap);
        } else {
            $book['content'] = $chapter['display'];
        }
        $allChannels = $chapterService->publicChannels((int) $bookId, (int) $paraId);
        $commentaryChannels = array_filter($allChannels, function ($channel) {
            return $channel['type'] === 'commentary';
        });
        $channels = array_filter($allChannels, function ($channel) {
            return $channel['type'] !== 'commentary';
        });

        $editor_link = config('mint.server.dashboard_base_path')
            ."/workspace/tipitaka/chapter/{$id}?channel={$channelId}";

        $view = view('library.book.read', compact('book', 'channels', 'editor_link', 'commentaryChannels'));

        return $view;
    }

    /**
     * 解析选中 chapter 的实际显示区间。
     *
     * ES 中每个 chapter 节点仅保存「本节点 → 下一节点」之间的正文，因此选中含
     * 子 chapter 的父节点时其自身文档往往只有标题。为完整呈现内容，这里把选中
     * chapter 覆盖区间内的所有 chapter 节点聚合为一个显示单元：
     *  - 选中节点 chapter_strlen 不超过阈值：直接使用其覆盖区间
     *    [paragraph, paragraph + chapter_len - 1]；
     *  - 否则向后下钻，找到第一个不超过阈值的子 chapter，以该子 chapter 的结束
     *    段落作为区间终点（起点仍为选中段落，保留上层标题作为阅读上下文）。
     *
     * @return array{current: PaliText, start: int, end: int, chapters: Collection<int, PaliText>}
     */
    private function resolveDisplayRange(int $book, int $para): array
    {
        $currBook = $this->bookStart($book, $para);
        $bookStart = $currBook->paragraph;
        $bookEnd = $currBook->paragraph + $currBook->chapter_len - 1;

        // 本书内全部 chapter 节点（level < 8，即 book/chapter/subhead 等可导航节点）
        $paragraphs = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$bookStart, $bookEnd])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->get();

        $curr = $paragraphs->firstWhere('paragraph', $para);
        $current = $curr;
        $endParagraph = $curr->paragraph + $curr->chapter_len - 1;

        if ($curr->chapter_strlen > $this->maxChapterStrlen) {
            // 选中节点过大：向后下钻，找到第一个不超过阈值的子 chapter
            foreach ($paragraphs as $key => $paragraph) {
                if ($paragraph->paragraph <= $curr->paragraph) {
                    continue;
                }
                if ($paragraph->chapter_strlen <= $this->maxChapterStrlen) {
                    $endParagraph = $paragraph->paragraph + $paragraph->chapter_len - 1;
                    $current = $paragraph;
                    break;
                }
                if ($paragraph->level <= $curr->level) {
                    // 已离开选中节点的子树，无法继续下钻，止步于上一个节点
                    $endParagraph = $paragraphs[$key - 1]->paragraph + $paragraphs[$key - 1]->chapter_len - 1;
                    $current = $paragraph;
                    break;
                }
            }
        }

        $start = $curr->paragraph;
        $end = $endParagraph;

        // 区间内的全部 chapter 节点，用于聚合 ES 内容
        $chapters = $paragraphs->filter(function ($paragraph) use ($start, $end) {
            return $paragraph->paragraph >= $start && $paragraph->paragraph <= $end;
        })->values();

        return compact('current', 'start', 'end', 'chapters');
    }

    /**
     * 聚合区间内全部 chapter 节点的 ES 内容。
     *
     * 逐个按 ES 文档 id（tipitaka_chapter_{book}-{paragraph}_{channel}）获取并按段落
     * 顺序拼接 display；缺失或获取失败的节点跳过。选中（即传入 $selectedId 的）
     * 节点同时提供页面标题与分类。
     *
     * @param  Collection<int, PaliText>  $chapters
     * @return array{display: string, title: string, category: array}
     */
    private function fetchRangeContent(int $book, string $channelId, $chapters, string $selectedId): array
    {
        $display = '';
        $title = '';
        $category = [];

        foreach ($chapters as $chapter) {
            $openSearchId = "tipitaka_chapter_{$book}-{$chapter->paragraph}_{$channelId}";

            try {
                $doc = HitItemDTO::fromArray($this->searchService->get($openSearchId))->toArray();
            } catch (\Throwable $th) {
                continue;
            }

            $display .= $doc['display'] ?? '';

            if ("{$book}-{$chapter->paragraph}" === $selectedId) {
                $title = $doc['title'] ?? '';
                $category = $doc['category'] ?? [];
            }
        }

        return compact('display', 'title', 'category');
    }

    private function loadBook(string $id)
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
            'id' => $book->uid,
            'title' => $title,
            'author' => $book->channel->name,
            'publisher' => $book->channel->owner,
            'type' => __('label.'.$book->channel->type),
            'category_id' => 11,
            'cover' => '/assets/images/cover/1/214.jpg',
            'description' => $book->summary ?? '',
            'language' => __('language.'.$book->channel->lang),
        ];
    }

    private function getBookTitle(int $book, int $paragraph, string $channelId)
    {
        $bookTopPara = $this->paliTextService->getBookPara($book, $paragraph)->paragraph;
        $title = ProgressChapter::where('book', $book)
            ->where('para', $bookTopPara)
            ->where('channel_id', $channelId)
            ->value('title');
        if (empty($title)) {
            $title = PaliText::where('book', $book)
                ->where('paragraph', $bookTopPara)
                ->value('toc');
        }

        return $title;
    }

    /**
     * @param  array{0: int, 1: int}|null  $activeRange  实际显示的段落区间 [start, end]；
     *                                                   传入时区间内的所有 chapter 均高亮（active），
     *                                                   用于聚合显示多个 chapter 的场景。不传则仅高亮选中段落。
     */
    private function getBookToc(int $book, int $paragraph, string $channelId, $minLevel = 2, $maxLevel = 2, ?array $activeRange = null): array
    {
        $currBook = $this->bookStart($book, $paragraph);

        $start = $currBook->paragraph;
        $end = $currBook->paragraph + $currBook->chapter_len - 1;

        $paliTexts = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$start, $end])
            ->whereBetween('level', [$minLevel, $maxLevel])
            ->orderBy('paragraph')
            ->get();

        if ($paliTexts->isEmpty()) {
            return [];
        }

        $chapters = ProgressChapter::where('book', $book)
            ->whereBetween('para', [$start, $end])
            ->where('channel_id', $channelId)
            ->orderBy('para')
            ->get();

        // keyBy 建索引，map 里 O(1) 查找，完全避免 toArray() 序列化和 array_filter O(n×m) 扫描
        $chaptersIndexed = $chapters->keyBy('para');

        // 当前阅读章节的 toc id，用于高亮（active）与折叠展开（hide）
        $currentId = "{$book}-{$paragraph}";

        // 折叠逻辑：列表有序，父节点 = 之前最近的更小 level 节点
        // 参见 AnthologyReadController::buildCollapsedToc()
        $parents = [];  // id => parent_id|null
        $stack = [];  // 祖先栈 [ ['id'=>..., 'level'=>...], ... ]
        foreach ($paliTexts as $paliText) {
            $id = "{$paliText->book}-{$paliText->paragraph}";
            $level = (int) $paliText->level;
            while (! empty($stack) && $stack[count($stack) - 1]['level'] >= $level) {
                array_pop($stack);
            }
            $parents[$id] = empty($stack) ? null : $stack[count($stack) - 1]['id'];
            $stack[] = ['id' => $id, 'level' => $level];
        }

        // 当前节点的祖先链
        $ancestorSet = [];
        $cursor = $currentId;
        while (! empty($parents[$cursor])) {
            $cursor = $parents[$cursor];
            $ancestorSet[$cursor] = true;
        }

        // 需要展开子节点的集合 = 祖先链 + 当前节点
        $expandParentSet = $ancestorSet;
        $expandParentSet[$currentId] = true;

        // 顶层 level（列表中最浅的一层，折叠模式下始终可见）
        $topLevel = (int) $paliTexts->min('level');

        return $paliTexts->map(function ($paliText) use ($chaptersIndexed, $channelId, $currentId, $parents, $expandParentSet, $topLevel, $activeRange) {
            $id = "{$paliText->book}-{$paliText->paragraph}";
            $level = (int) $paliText->level;
            $title = $paliText->toc;
            $summary = '';
            $progress = 0;
            $disabled = true;

            /** @var ProgressChapter|null $chapter */
            $chapter = $chaptersIndexed->get($paliText->paragraph);
            if ($chapter) {
                if (! empty($chapter->title)) {
                    $title = $chapter->title;
                }
                if (! empty($chapter->summary)) {
                    $summary = $chapter->summary;
                }
                $progress = (int) ($chapter->progress * 100);
                $disabled = false;
            }

            $parentId = $parents[$id];
            // 折叠模式可见：顶层节点，或父节点在展开集合中（祖先链 / 当前节点的直接子节点）
            $visible = $level === $topLevel
                || ($parentId !== null && isset($expandParentSet[$parentId]));

            // 聚合显示时高亮区间内所有 chapter，否则仅高亮选中段落
            $active = $activeRange !== null
                ? ($paliText->paragraph >= $activeRange[0] && $paliText->paragraph <= $activeRange[1])
                : ($id === $currentId);

            return [
                'id' => $id,
                'channel' => $channelId,
                'title' => $title,
                'summary' => $summary,
                'progress' => $progress,
                'level' => $level,
                'disabled' => $disabled,
                'active' => $active,
                'hide' => ! $visible,
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
        // 与正文显示共用同一区间解析，保证分页边界与实际展示内容一致
        $range = $this->resolveDisplayRange($book, $para);
        $start = $range['start'];
        $end = $range['end'];

        // next/prev 以显示区间为边界，而非某个节点的层级：
        // 聚合显示子 chapter 时，下一页应是区间 end 之后的第一个可导航段落，
        // 上一页应是区间 start 之前最近的可导航段落。若按 current 的 level 取同级节点，
        // 会在章节边界处跳过父级标题页或落入子节点。
        $nextPali = $this->nextChapter($book, $end);
        $prevPali = $this->prevChapter($book, $start);

        $next = null;
        if ($nextPali) {
            $nextTranslation = ProgressChapter::with('channel.owner')
                ->where('book', $nextPali->book)
                ->where('para', $nextPali->paragraph)
                ->where('channel_id', $channelId)
                ->first();
            if ($nextTranslation) {
                if (! empty($nextTranslation->title)) {
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
                if (! empty($prevTranslation->title)) {
                    $prev['title'] = $prevTranslation->title;
                } else {
                    $prev['title'] = $prevPali->toc;
                }
                $prev['id'] = "{$prevPali->book}-{$prevPali->paragraph}";
            }
        }

        return compact('start', 'end', 'next', 'prev');
    }

    /**
     * 显示区间之后的第一个可导航 chapter 节点（level < 8）。
     */
    public function nextChapter(int $book, int $endParagraph): ?PaliText
    {
        return PaliText::where('book', $book)
            ->where('paragraph', '>', $endParagraph)
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->first();
    }

    /**
     * 显示区间之前最近的一个可导航 chapter 节点（level < 8）。
     */
    public function prevChapter(int $book, int $startParagraph): ?PaliText
    {
        return PaliText::where('book', $book)
            ->where('paragraph', '<', $startParagraph)
            ->where('level', '<', 8)
            ->orderBy('paragraph', 'desc')
            ->first();
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
