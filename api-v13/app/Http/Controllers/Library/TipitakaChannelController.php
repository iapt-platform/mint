<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\UserInfo;
use App\Services\TipitakaCatalogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

/**
 * library 三藏栏目：按译本（channel）浏览。
 *
 * - /library/tipitaka/channels                         公开 channel 列表，按 type 分组
 * - /library/tipitaka/channels/{channel}               该 channel 收录的作品（分页）
 * - /library/tipitaka/channels/{channel}/category/{id} 该 channel 的分类目录树，每部作品带进度
 */
class TipitakaChannelController extends Controller
{
    private const PER_PAGE = 8;

    private const CHANNELS_PER_PAGE = 20;

    public function __construct(
        protected TipitakaCatalogService $catalog,
    ) {}

    /**
     * 公开 channel 列表：一个列表，按收录作品数降序，分页。可用 ?type= / ?channel_lang= 过滤。
     *
     * 语言过滤不能叫 lang：SetLocale 中间件用 ?lang= 切换界面语言。
     */
    public function index(Request $request): View
    {
        $selectedType = $request->query('type', 'all');
        $selectedLang = $request->query('channel_lang', 'all');
        $type = $selectedType === 'all' ? null : $selectedType;
        $lang = $selectedLang === 'all' ? null : $selectedLang;
        $paginator = $this->catalog->publicChannels($lang, self::CHANNELS_PER_PAGE, $type)->withQueryString();
        $owners = $this->owners($paginator->getCollection()->pluck('owner_uid')->all());

        $channels = $paginator->getCollection()->map(fn ($c) => [
            ...$c,
            'owner' => $owners->get($c['owner_uid']),
            'url' => route('library.tipitaka.channels.show', ['channel' => $c['uid']]),
        ])->all();
        $types = $this->catalog->filterOptions($this->catalog->publicChannelCounts('type', ['lang' => $lang]), 'labels');
        $languages = $this->catalog->filterOptions($this->catalog->publicChannelCounts('lang', ['type' => $type]));

        return view('library.tipitaka.channels.index', compact('channels', 'paginator', 'types', 'languages', 'selectedType', 'selectedLang'));
    }

    /**
     * 单个 channel 收录的作品，默认按经典顺序（book_titles.sn），可 ?sort=updated|progress。
     */
    public function show(Request $request, string $channel): View
    {
        $channelInfo = $this->channelInfo($channel);
        $progress = $this->catalog->channelProgress($channel);
        $works = $this->worksOf($progress);

        $sort = $request->query('sort', 'canon');
        $works = match ($sort) {
            'updated' => $works->sortByDesc(fn ($w) => $progress["{$w['book']}-{$w['para']}"]['updated_at']),
            'progress' => $works->sortByDesc(fn ($w) => $progress["{$w['book']}-{$w['para']}"]['progress']),
            default => $works,
        };

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = (new LengthAwarePaginator(
            $works->forPage($page, self::PER_PAGE)->values(),
            $works->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url()]
        ))->withQueryString();

        $books = $paginator->getCollection()
            ->map(fn ($w) => $this->catalog->bookCard($w, $channelInfo, $progress["{$w['book']}-{$w['para']}"]))
            ->all();

        $sortList = [
            ['key' => 'canon', 'label' => __('library.sort_canon')],
            ['key' => 'updated', 'label' => __('library.badge_updated')],
            ['key' => 'progress', 'label' => __('library.sort_progress')],
        ];

        $topCategories = $this->topCategories($works->all());

        return view('library.tipitaka.channels.show', compact('channelInfo', 'books', 'paginator', 'sort', 'sortList', 'topCategories'));
    }

    /**
     * 单个 channel 的分类目录树。顶部切换「全部」与四个藏（不带 id = 全部），树里的节点只是分组（不可点），
     * 末级分类是卡片，卡片里每部作品一条进度条。?all=1 时也列出未收录的作品（置灰）。
     */
    public function category(Request $request, string $channel, ?int $id = null): View
    {
        $channelInfo = $this->channelInfo($channel);
        // 全部：虚拟根节点，下面挂四个藏
        $node = $id === null ? ['id' => 0, 'name' => __('library.all')] : $this->catalog->category($id);
        abort_unless($node, 404);

        $progress = $this->catalog->channelProgress($channel);
        $showAll = $request->boolean('all');
        $names = $this->catalog->localNames($this->catalog->categories(), Cookie::get('language') ?? 'en');

        $tree = $this->buildNode($node, $progress, $names, $showAll, $channel);
        $topCategories = $this->topCategories($this->worksOf($progress)->all());
        $currentTop = $id === null ? null : $this->catalog->categoryPath($id)[0]['id'];

        return view('library.tipitaka.channels.category', compact('channelInfo', 'tree', 'topCategories', 'currentTop', 'showAll'));
    }

    /**
     * 递归构造目录树的一个节点。本 channel 在该节点下没有作品、且不显示未收录时返回 null。
     *
     * @param  array<string, array{progress: float, title: string|null}>  $progress
     * @param  array<string, string>  $names
     * @return array{id: int, name: string, pali: string, leaf: bool, mine: int, total: int, avg: int, works: list<array>, children: list<array>}|null
     */
    private function buildNode(array $node, array $progress, array $names, bool $showAll, string $channel): ?array
    {
        $children = $this->catalog->children($node['id']);
        $subtree = $this->catalog->worksInCategory($node['id']);
        $mine = array_values(array_filter($subtree, fn ($w) => isset($progress["{$w['book']}-{$w['para']}"])));
        if (! $showAll && empty($mine)) {
            return null;
        }

        // 直接挂在本节点上的作品（末级分类的全部作品，或非末级分类直接挂的少数几部）
        $own = array_filter($subtree, fn ($w) => $w['category_id'] === $node['id']);
        $works = [];
        foreach ($own as $work) {
            $row = $progress["{$work['book']}-{$work['para']}"] ?? null;
            if (! $row && ! $showAll) {
                continue;
            }
            $works[] = [
                'sn' => $work['sn'],
                'title' => ! empty($row['title']) ? $row['title'] : $work['title'],
                'progress' => $row ? (int) round($row['progress'] * 100) : null,
                'url' => route('library.tipitaka.channels.work', ['channel' => $channel, 'sn' => $work['sn']]),
            ];
        }

        $sum = array_sum(array_map(fn ($w) => $progress["{$w['book']}-{$w['para']}"]['progress'], $mine));

        return [
            'id' => $node['id'],
            'name' => $names[$node['name']] ?? $node['name'],
            'pali' => $node['name'],
            'leaf' => empty($children),
            'mine' => count($mine),
            'total' => count($subtree),
            'avg' => $mine ? (int) round($sum / count($mine) * 100) : 0,
            'works' => $works,
            'children' => array_values(array_filter(array_map(
                fn ($child) => $this->buildNode($child, $progress, $names, $showAll, $channel),
                $children
            ))),
        ];
    }

    /**
     * channel 头部信息；不存在 404。
     *
     * @return array{uid: string, name: string, type: string, lang: string, owner: UserInfo|null, works: int, updated_at: string|null}
     */
    private function channelInfo(string $uid): array
    {
        $channel = $this->catalog->channel($uid);
        abort_unless($channel instanceof Channel, 404);
        $progress = $this->catalog->channelProgress($uid);

        return [
            'uid' => $uid,
            'name' => $channel->name,
            'type' => $channel->type,
            'lang' => $channel->lang,
            'owner' => $channel->owner,
            'works' => count($progress),
            'updated_at' => collect($progress)->max('updated_at'),
        ];
    }

    /**
     * 该 channel 收录的作品，按 sn 排序。
     *
     * @param  array<string, mixed>  $progress
     * @return Collection<int, array<string, mixed>>
     */
    private function worksOf(array $progress): Collection
    {
        return collect($this->catalog->works())
            ->filter(fn ($w) => isset($progress["{$w['book']}-{$w['para']}"]))
            ->values();
    }

    /**
     * 四个藏及本 channel 在其下的作品数。
     *
     * @return list<array{id: int, name: string, count: int}>
     */
    private function topCategories(array $works): array
    {
        $tops = $this->catalog->children(0);
        $names = $this->catalog->localNames($tops, Cookie::get('language') ?? 'en');

        return array_map(function ($top) use ($works, $names) {
            $ids = $this->catalog->subtreeIds($top['id']);

            return [
                'id' => $top['id'],
                'name' => $names[$top['name']] ?? $top['name'],
                'count' => count(array_filter($works, fn ($w) => in_array($w['category_id'], $ids, true))),
            ];
        }, $tops);
    }

    /**
     * @param  list<string>  $uids
     * @return Collection<string, UserInfo>
     */
    private function owners(array $uids): Collection
    {
        return UserInfo::whereIn('userid', array_unique($uids))
            ->get(['userid', 'username', 'nickname'])
            ->keyBy(fn ($u) => (string) $u->userid);
    }
}
