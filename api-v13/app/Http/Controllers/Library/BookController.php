<?php

namespace App\Http\Controllers\Library;

use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;
use App\Http\Controllers\Controller;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Sentence;
use App\Services\ChapterService;
use App\Services\PaliContentService;
use App\Services\PaliTextService;
use App\Services\TipitakaCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
     * 构造函数，注入文本、段落内容与三藏目录服务
     */
    public function __construct(
        protected PaliTextService $paliTextService,
        protected PaliContentService $paliContentService,
        protected TipitakaCatalogService $catalog,
    ) {}

    /**
     * 作品详情：某 channel 的某部作品（work_id = book_titles.sn）。该 channel 没收录这部作品时 404。
     */
    public function show(string $channel, int $sn): View
    {
        $work = $this->catalog->work($sn);
        $channelModel = $this->catalog->channel($channel);
        abort_unless($work && $channelModel, 404);
        $progress = $this->catalog->channelProgress($channel)["{$work['book']}-{$work['para']}"] ?? null;
        abort_unless($progress, 404);

        $channelInfo = [
            'uid' => $channel,
            'name' => $channelModel->name,
            'type' => $channelModel->type,
            'lang' => $channelModel->lang,
            'owner' => $channelModel->owner,
        ];
        $book = $this->catalog->bookCard($work, $channelInfo, $progress);
        $book['channel'] = $channelInfo;
        $book['pali_title'] = $work['title'];
        $book['updated_at'] = $progress['updated_at'];
        $book['read_url'] = route('library.tipitaka.channels.work.read', ['channel' => $channel, 'sn' => $sn]);
        $book['contents'] = $this->getBookToc($work['book'], $work['para'], $channel, 2, 2, null, [$work['para'], $work['end']]);

        $names = $this->catalog->localNames($this->catalog->categories(), Cookie::get('language') ?? 'en');
        $book['categories'] = $work['category_id'] === null ? [] : array_map(fn ($c) => [
            'name' => $names[$c['name']] ?? $c['name'],
            'url' => route('library.tipitaka.channels.category', ['channel' => $channel, 'id' => $c['id']]),
        ], $this->catalog->categoryPath($work['category_id']));

        $otherVersions = array_map(fn ($c) => [
            ...$c,
            'url' => route('library.tipitaka.channels.work', ['channel' => $c['uid'], 'sn' => $sn]),
        ], $this->catalog->workChannels($work, $channel));

        // 同一分类下、本 channel 也收录了的其他作品
        $channelProgress = $this->catalog->channelProgress($channel);
        $relatedWorks = $work['category_id'] === null ? [] : collect($this->catalog->worksInCategory($work['category_id']))
            ->filter(fn ($w) => $w['sn'] !== $sn && isset($channelProgress["{$w['book']}-{$w['para']}"]))
            ->take(5)
            ->map(fn ($w) => [
                'title' => $channelProgress["{$w['book']}-{$w['para']}"]['title'] ?: $w['title'],
                'url' => route('library.tipitaka.channels.work', ['channel' => $channel, 'sn' => $w['sn']]),
            ])
            ->values()
            ->all();

        return view('library.tipitaka.show', compact('book', 'otherVersions', 'relatedWorks'));
    }

    /**
     * 阅读入口：作品区间内第一个本 channel 有译文的章节（level 2–7），没有就从作品起点读。
     * 目标随翻译进度变化，所以是 302。
     */
    public function readWork(string $channel, int $sn): RedirectResponse
    {
        $work = $this->catalog->work($sn);
        abort_unless($work && ChannelApi::getById($channel), 404);

        $chapterParas = PaliText::where('book', $work['book'])
            ->whereBetween('paragraph', [$work['para'], $work['end']])
            ->whereBetween('level', [2, 7])
            ->pluck('paragraph');
        $first = ProgressChapter::where('channel_id', $channel)
            ->where('book', $work['book'])
            ->whereIn('para', $chapterParas)
            ->min('para');

        return redirect()->route('library.tipitaka.channels.chapter', [
            'channel' => $channel,
            'chapter' => "{$work['book']}-".($first ?? $work['para']),
        ]);
    }

    /**
     * 旧地址 /library/tipitaka/{book}-{para}/read?channel=x → 新地址（301）。
     * 没带 channel 的读巴利原文；其余查询参数（如 comm）原样带过去。
     */
    public function legacyRead(Request $request, string $id): RedirectResponse
    {
        $channelId = $request->query('channel') ?: ChannelApi::getSysChannel('_System_Pali_VRI_');
        abort_unless(Str::isUuid($channelId) && ChannelApi::getById($channelId), 404);

        return redirect()->route('library.tipitaka.channels.chapter', [
            'channel' => $channelId,
            'chapter' => $id,
            ...$request->except('channel'),
        ], 301);
    }

    /**
     * 旧地址 /library/tipitaka/{progress_chapters.uid} → 该 channel 的作品详情（301）。
     */
    public function legacyShow(string $id): RedirectResponse
    {
        $row = ProgressChapter::find($id);
        $work = $row ? $this->catalog->workAt((int) $row->book, (int) $row->para) : null;
        abort_unless($work, 404);

        return redirect()->route('library.tipitaka.channels.work', ['channel' => $row->channel_id, 'sn' => $work['sn']], 301);
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

    /**
     * 阅读页 /library/tipitaka/channels/{channel}/chapter/{book}-{para}。
     */
    public function read(Request $request, string $channel, string $chapter): View
    {
        $channelId = $channel;
        $id = $chapter;
        // 读的是巴利原文（旧地址没带 channel 时也落到这里），提示可切换版本
        $isDefaultChannel = $channelId === ChannelApi::getSysChannel('_System_Pali_VRI_');

        abort_unless(preg_match('/^\d+-\d+$/', $id), 404);
        [$bookId, $paraId] = explode('-', $id);
        $bookId = (int) $bookId;
        $paraId = (int) $paraId;

        $channel = ChannelApi::getById($channelId);
        abort_unless($channel, 404);

        // 解析选中 chapter 的实际显示区间（起始/终止段落号），再按段落号聚合 HTML，
        // 避免选中父 chapter 时仅显示一个标题。
        $range = $this->resolveDisplayRange($bookId, $paraId);
        $chapter = $this->fetchRangeContent($bookId, $channelId, $range['start'], $range['end'], $paraId);

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
        $studio = StudioApi::getById($channel['studio_id']);
        $book['categories'] = $chapter['category'];
        $book['title'] = $chapter['title'];
        $book['author'] = $channel['name'];
        $book['is_original'] = in_array($channel['type'], ['original', 'wbw'], true);
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
        $channels = array_map(fn ($channel) => [
            ...$channel,
            'url' => route('library.tipitaka.channels.chapter', ['channel' => $channel['id'], 'chapter' => $id, ...$request->query()]),
        ], array_filter($allChannels, function ($channel) {
            return $channel['type'] !== 'commentary';
        }));

        $work = $this->catalog->workAt($bookId, $paraId);
        $book['work_url'] = $work
            ? route('library.tipitaka.channels.work', ['channel' => $channelId, 'sn' => $work['sn']])
            : null;

        $editor_link = config('mint.server.dashboard_base_path')
            ."/workspace/tipitaka/chapter/{$id}?channel={$channelId}";

        $view = view('library.book.read', compact('book', 'channels', 'editor_link', 'commentaryChannels', 'isDefaultChannel', 'channelId'));

        return $view;
    }

    /**
     * 解析选中 chapter 的实际显示区间，返回区间的起始与终止段落号。
     *
     * ES 中每个 chapter 节点仅保存「本节点 → 下一节点」之间的正文，因此选中含
     * 子 chapter 的父节点时其自身文档往往只有标题。为完整呈现内容，这里按阈值
     * 决定显示区间终点：
     *  - 选中节点 chapter_strlen 不超过阈值：终点为
     *    [paragraph + chapter_len - 1]；
     *  - 否则向后下钻，找到第一个不超过阈值的子 chapter，以该子 chapter 的结束
     *    段落作为区间终点（起点仍为选中段落，保留上层标题作为阅读上下文）。
     *
     * @return array{start: int, end: int}
     */
    private function resolveDisplayRange(int $book, int $para): array
    {
        $currBook = $this->bookStart($book, $para);
        abort_if($currBook === null, 404);
        $bookStart = $currBook->paragraph;
        $bookEnd = $currBook->paragraph + $currBook->chapter_len - 1;

        // 本书内全部 chapter 节点（level < 8，即 book/chapter/subhead 等可导航节点）
        $paragraphs = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$bookStart, $bookEnd])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->get();

        $curr = $paragraphs->firstWhere('paragraph', $para);
        // 选中的不是可导航的 chapter 节点（如正文段落 level=100）
        abort_if($curr === null, 404);
        $endParagraph = $curr->paragraph + $curr->chapter_len - 1;

        if ($curr->chapter_strlen > $this->maxChapterStrlen) {
            // 选中节点过大：向后下钻，找到第一个不超过阈值的子 chapter
            foreach ($paragraphs as $key => $paragraph) {
                if ($paragraph->paragraph <= $curr->paragraph) {
                    continue;
                }
                if ($paragraph->chapter_strlen <= $this->maxChapterStrlen) {
                    $endParagraph = $paragraph->paragraph + $paragraph->chapter_len - 1;
                    break;
                }
                if ($paragraph->level <= $curr->level) {
                    // 已离开选中节点的子树，无法继续下钻，止步于上一个节点
                    $endParagraph = $paragraphs[$key - 1]->paragraph + $paragraphs[$key - 1]->chapter_len - 1;
                    break;
                }
            }
        }

        return [
            'start' => $curr->paragraph,
            'end' => $endParagraph,
        ];
    }

    /**
     * 聚合区间内全部段落的阅读模式 HTML。
     *
     * 与 TipitakaReadParaController 一致，逐段调用 PaliContentService::readParagraph
     * 获取 HTML 并按段落顺序拼接；缺失或获取失败的段落跳过。章节标题优先取
     * ProgressChapter.title，回退到 PaliText.toc。
     *
     * @return array{display: string, title: string, category: array}
     */
    private function fetchRangeContent(int $book, string $channelId, int $start, int $end, int $selectedPara): array
    {
        $display = '';
        foreach (range($start, $end) as $para) {
            $paragraph = $this->paliContentService->readParagraph($book, $para, $channelId, 'html');
            if (empty($paragraph['display'])) {
                continue;
            }
            $display .= $paragraph['display'];
        }

        $title = ProgressChapter::where('book', $book)
            ->where('para', $selectedPara)
            ->where('channel_id', $channelId)
            ->value('title');
        if (empty($title)) {
            $title = PaliText::where('book', $book)
                ->where('paragraph', $selectedPara)
                ->value('toc');
        }

        return [
            'display' => $display,
            'title' => $title ?? '',
            'category' => [],
        ];
    }

    public function toggleTheme(Request $request)
    {
        $theme = $request->input('theme', 'light');
        session(['theme' => $theme]);

        return response()->json(['status' => 'success']);
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
     * @param  array{0: int, 1: int}|null  $bounds  目录的段落范围；不传取所在 level-1 节点（整本书）。
     *                                              作品详情页传作品区间（一个 level-1 节点里可能有多部作品）。
     */
    private function getBookToc(int $book, int $paragraph, string $channelId, $minLevel = 2, $maxLevel = 2, ?array $activeRange = null, ?array $bounds = null): array
    {
        if ($bounds !== null) {
            [$start, $end] = $bounds;
        } else {
            $currBook = $this->bookStart($book, $paragraph);
            $start = $currBook->paragraph;
            $end = $currBook->paragraph + $currBook->chapter_len - 1;
        }

        $paliTexts = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$start, $end])
            ->whereBetween('level', [$minLevel, $maxLevel])
            ->orderBy('paragraph')
            ->get();
        // Log::debug('toc', ['toc' => $paliTexts->toArray()]);
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

        // 高亮(active)集合：聚合显示时为区间内所有 chapter，否则仅选中段落
        $activeSet = [];
        foreach ($paliTexts as $paliText) {
            $id = "{$paliText->book}-{$paliText->paragraph}";
            $active = $activeRange !== null
                ? ($paliText->paragraph >= $activeRange[0] && $paliText->paragraph <= $activeRange[1])
                : ($id === $currentId);
            if ($active) {
                $activeSet[$id] = true;
            }
        }

        // 需要展开子节点的集合 = 每个 active 节点及其祖先链。
        // 聚合显示跨多级时，区间内各级 chapter 都需展开，其子级才能完整显示，
        // 否则仅围绕单个当前节点展开，最多只能显示两级。
        $expandParentSet = [];
        foreach (array_keys($activeSet) as $activeId) {
            $expandParentSet[$activeId] = true;
            $cursor = $activeId;
            while (! empty($parents[$cursor])) {
                $cursor = $parents[$cursor];
                $expandParentSet[$cursor] = true;
            }
        }

        // 顶层 level（列表中最浅的一层，折叠模式下始终可见）
        $topLevel = (int) $paliTexts->min('level');

        $output = $paliTexts->map(function ($paliText) use ($chaptersIndexed, $channelId, $parents, $expandParentSet, $activeSet, $topLevel) {
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

            $active = isset($activeSet[$id]);

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

        // Log::debug('toc output', ['data' => $output]);
        return $output;
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
}
