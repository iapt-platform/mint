<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\ProgressChapter;
use App\Services\TipitakaCatalogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * library 三藏栏目：首页与按分类浏览（不限 channel）。
 *
 * 按译本浏览见 TipitakaChannelController，作品详情与阅读见 BookController。
 */
class TipitakaController extends Controller
{
    private const PER_PAGE = 8;

    public function __construct(
        protected TipitakaCatalogService $catalog,
    ) {}

    /**
     * 三藏首页：分类（两级卡片）、译本、最近更新。
     *
     * 旧地址 /library/tipitaka?channel=x（dashboard「我的 channel」在用）301 到该 channel 的页面。
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (Str::isUuid($request->query('channel'))) {
            return redirect()->route('library.tipitaka.channels.show', ['channel' => $request->query('channel')], 301);
        }

        $locale = Cookie::get('language') ?? 'en';
        $names = $this->catalog->localNames($this->catalog->categories(), $locale);

        $categoryData = array_map(fn ($top) => [
            'category' => [...$top, 'local_name' => $names[$top['name']] ?? $top['name']],
            'children' => array_map(
                fn ($child) => [...$child, 'local_name' => $names[$child['name']] ?? $child['name']],
                $this->catalog->children($top['id'])
            ),
        ], $this->catalog->children(0));

        $channels = $this->catalog->publicChannels(null, 8)->items();
        $recentBooks = $this->recentBooks($this->catalog->works(), 12);

        return view('library.tipitaka.index', compact('categoryData', 'channels', 'recentBooks'));
    }

    /**
     * 分类页：子分类 + 过滤器 + 该分类下所有公开 channel 的作品版本（分页）。
     *
     * 末级分类的「子分类」位置列出该类下的作品，点开进作品版本页。
     * 旧链接带 ?channel= 的 301 到该 channel 的分类视图，带 ?book= 的 301 到作品版本页。
     */
    public function category(Request $request, int $id): View|RedirectResponse
    {
        if (Str::isUuid($request->query('channel'))) {
            return redirect()->route('library.tipitaka.channels.category', ['channel' => $request->query('channel'), 'id' => $id], 301);
        }
        if (preg_match('/^(\d+)-(\d+)$/', (string) $request->query('book'), $m)
            && ($work = $this->catalog->workAt((int) $m[1], (int) $m[2]))) {
            return redirect()->route('library.tipitaka.work', ['sn' => $work['sn']], 301);
        }

        $currentCategory = $this->catalog->category($id);
        abort_unless($currentCategory, 404);

        $localize = $this->localizer();
        $currentCategory = $localize($currentCategory);
        $breadcrumbs = array_map($localize, $this->catalog->categoryPath($id));
        $works = $this->catalog->worksInCategory($id);

        $subCategories = array_map(fn ($c) => [
            ...$localize($c),
            'url' => route('library.tipitaka.category', ['id' => $c['id']]),
            'is_work' => false,
        ], $this->catalog->children($id));
        if (empty($subCategories)) {
            foreach ($works as $work) {
                $subCategories[] = [
                    'name' => $work['title'],
                    'url' => route('library.tipitaka.work', ['sn' => $work['sn']]),
                    'is_work' => true,
                ];
            }
        }

        $sideTree = $this->sideTree(0, array_column($breadcrumbs, 'id'), $id, $localize);
        $recentBooks = $this->recentBooks($works, 5);
        $activeChannels = $this->activeChannels($works, 5);

        return view('library.tipitaka.category', [
            ...$this->versionList($request, $works),
            ...compact('currentCategory', 'subCategories', 'breadcrumbs', 'sideTree', 'recentBooks', 'activeChannels'),
        ]);
    }

    /**
     * 作品版本页：一部作品（book_titles.sn）在所有公开 channel 里的版本。
     * 版式同译本列表页：按 type 分组，左栏可按语言过滤，每行带该版本的进度。
     */
    public function work(Request $request, int $sn): View
    {
        $work = $this->catalog->work($sn);
        abort_unless($work, 404);

        $all = array_map(fn ($c) => [
            ...$c,
            'url' => route('library.tipitaka.channels.work', ['channel' => $c['uid'], 'sn' => $sn]),
        ], $this->catalog->workChannels($work));
        // 语言过滤不能叫 lang：SetLocale 中间件用 ?lang= 切换界面语言
        $selectedLang = $request->query('channel_lang', 'all');
        $versions = $selectedLang === 'all' ? $all : array_values(array_filter($all, fn ($c) => $c['lang'] === $selectedLang));
        $groups = $this->catalog->groupByType($versions);
        $languages = $this->catalog->filterOptions(collect($all)->countBy('lang')->all());

        $localize = $this->localizer();
        $breadcrumbs = $work['category_id'] === null
            ? []
            : array_map($localize, $this->catalog->categoryPath($work['category_id']));
        $kind = $work['kind'] ? __('library.'.$work['kind']) : null;

        return view('library.tipitaka.work', compact('work', 'breadcrumbs', 'groups', 'languages', 'selectedLang', 'kind'));
    }

    /**
     * 分类页的版本列表：过滤器选项、当前过滤、排序与分页后的书卡片。
     *
     * @param  list<array<string, mixed>>  $works
     * @return array{selected: array{type: string, lang: string, sort: string}, filterOptions: array, sortList: array, paginator: LengthAwarePaginator, categoryBooks: list<array>, totalCount: int}
     */
    private function versionList(Request $request, array $works): array
    {
        $selected = [
            'type' => $request->query('type', 'all'),
            // 不能叫 lang：SetLocale 中间件用 ?lang= 切换界面语言
            'lang' => $request->query('channel_lang', 'all'),
            'sort' => $request->query('sort', 'new'),
        ];

        $base = $this->versionsQuery($works);
        $filterOptions = [
            'types' => $this->filterOptions(clone $base, 'type', 'labels'),
            'languages' => $this->filterOptions(clone $base, 'lang', 'language'),
        ];

        $query = (clone $base)->select('progress_chapters.*')->with('channel.owner')
            ->when($selected['type'] !== 'all', fn ($q) => $q->whereHas('channel', fn ($c) => $c->where('type', $selected['type'])))
            ->when($selected['lang'] !== 'all', fn ($q) => $q->whereHas('channel', fn ($c) => $c->where('lang', $selected['lang'])));
        $query = $selected['sort'] === 'progress'
            ? $query->orderByDesc('progress')
            : $query->orderByDesc('updated_at');

        $paginator = $query->paginate(self::PER_PAGE)->withQueryString();

        return [
            'selected' => $selected,
            'filterOptions' => $filterOptions,
            'sortList' => [
                ['key' => 'new', 'label' => __('library.badge_updated')],
                ['key' => 'progress', 'label' => __('library.sort_progress')],
            ],
            'paginator' => $paginator,
            'categoryBooks' => $this->toCards($paginator->getCollection(), $works),
            'totalCount' => $paginator->total(),
        ];
    }

    /**
     * 左栏分类树：顶级分类全列；沿当前分类的路径逐级展开，当前分类也展开它的下一级。
     *
     * @param  list<int>  $pathIds  从顶级到当前分类的 id
     * @return list<array{id: int, name: string, active: bool, open: bool, children: list<array>}>
     */
    private function sideTree(int $parentId, array $pathIds, int $currentId, \Closure $localize): array
    {
        return array_map(fn ($c) => [
            'id' => $c['id'],
            'name' => $localize($c)['name'],
            'active' => $c['id'] === $currentId,
            // 在当前路径上（祖先或自身），加粗以区分层级
            'open' => in_array($c['id'], $pathIds, true),
            'children' => in_array($c['id'], $pathIds, true)
                ? $this->sideTree($c['id'], $pathIds, $currentId, $localize)
                : [],
        ], $this->catalog->children($parentId));
    }

    /**
     * 分类名本地化：查术语表，查不到用巴利名。
     *
     * @return \Closure(array): array
     */
    private function localizer(): \Closure
    {
        $names = $this->catalog->localNames($this->catalog->categories(), Cookie::get('language') ?? 'en');

        return fn (array $c) => [...$c, 'name' => $names[$c['name']] ?? $c['name']];
    }

    /**
     * 这些作品在公开 channel 里的全部版本（progress_chapters 的作品根节点行）。
     *
     * 两步：作品根节点 (book, para) 取自目录缓存，不查库；再和 progress_chapters 做 join。
     * 根节点写成 VALUES 临时表 join，而不是 whereIns 的 (book, para) in ((..),(..)…)：
     * 没有索引时都要全表扫描，但 join 走哈希，281 个根节点时 94ms → 20ms（2026-11-06 实测）。
     * 需要模型的调用方要自己 select('progress_chapters.*')，否则 work_roots 的列会混进来。
     *
     * TODO: 缺 progress_chapters (book, para) 索引，仍是全表扫描，见 TipitakaCatalogService 类注释。
     *
     * @param  list<array{book: int, para: int}>  $works
     */
    private function versionsQuery(array $works): Builder
    {
        $query = ProgressChapter::query()->whereHas('channel', fn ($q) => $q->where('status', 30));
        if (empty($works)) {
            return $query->whereRaw('1 = 0');
        }

        // book / para 是目录里的整数，强转后直接拼进 SQL，不会注入
        $values = implode(',', array_map(fn ($w) => '('.(int) $w['book'].','.(int) $w['para'].')', $works));

        return $query->join(
            DB::raw("(values {$values}) as work_roots(book, para)"),
            fn ($join) => $join->on('work_roots.book', '=', 'progress_chapters.book')
                ->on('work_roots.para', '=', 'progress_chapters.para')
        );
    }

    /**
     * 过滤器选项：按 channel 的某一列聚合版本数。
     *
     * @return list<array{value: string, label: string, count: int}>
     */
    private function filterOptions(Builder $base, string $column, string $langFile): array
    {
        $rows = $base->join('channels', 'channels.uid', '=', 'progress_chapters.channel_id')
            ->groupBy("channels.{$column}")
            ->orderByRaw('count(*) desc')
            ->get([DB::raw("channels.{$column} as value"), DB::raw('count(*) as count')]);

        return [
            ['value' => 'all', 'label' => __('library.all'), 'count' => (int) $rows->sum('count')],
            ...$rows->map(fn ($row) => [
                'value' => $row->value,
                'label' => __("{$langFile}.{$row->value}"),
                'count' => (int) $row->count,
            ])->all(),
        ];
    }

    /**
     * progress_chapters 行 → 书卡片。
     *
     * @param  iterable<ProgressChapter>  $rows
     * @param  list<array<string, mixed>>  $works
     * @return list<array<string, mixed>>
     */
    private function toCards(iterable $rows, array $works): array
    {
        $byRoot = collect($works)->keyBy(fn ($w) => "{$w['book']}-{$w['para']}");
        $cards = [];
        foreach ($rows as $row) {
            $work = $byRoot->get("{$row->book}-{$row->para}");
            if (! $work || ! $row->channel) {
                continue;
            }
            $cards[] = $this->catalog->bookCard($work, [
                'uid' => (string) $row->channel_id,
                'name' => $row->channel->name,
                'type' => $row->channel->type,
                'lang' => $row->channel->lang,
                'owner' => $row->channel->owner,
            ], ['progress' => (float) $row->progress, 'title' => $row->title, 'summary' => $row->summary]);
        }

        return $cards;
    }

    /**
     * 这些作品最近更新的版本。
     *
     * @return list<array<string, mixed>>
     */
    private function recentBooks(array $works, int $limit): array
    {
        $rows = $this->versionsQuery($works)->select('progress_chapters.*')->with('channel.owner')
            ->orderByDesc('updated_at')
            ->take($limit)
            ->get();

        return $this->toCards($rows, $works);
    }

    /**
     * 这些作品里收录最多的公开 channel，给右栏「本类译本」用。
     *
     * @return list<array{uid: string, name: string, lang: string, count: int}>
     */
    private function activeChannels(array $works, int $limit): array
    {
        return $this->versionsQuery($works)
            ->join('channels', 'channels.uid', '=', 'progress_chapters.channel_id')
            ->groupBy('channels.uid', 'channels.name', 'channels.lang')
            ->orderByRaw('count(*) desc')
            ->take($limit)
            ->get([DB::raw('channels.uid as uid'), 'channels.name', 'channels.lang', DB::raw('count(*) as count')])
            ->map(fn ($row) => [
                'uid' => (string) $row->uid,
                'name' => $row->name,
                'lang' => $row->lang,
                'count' => (int) $row->count,
            ])
            ->all();
    }
}
