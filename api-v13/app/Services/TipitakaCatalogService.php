<?php

namespace App\Services;

use App\Facades\RedisCache;
use App\Models\BookTitle;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\TagMap;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * library 三藏栏目的目录：分类树与「作品」。
 *
 * 作品 = book_titles 的一行，URL 里用 sn 作 id。作品名取起点段落的 pali_texts.toc；
 * book_titles.title 很多存的是丛书名（如 141-3 是 samantapāsādikā，实为 (SP) Cūḷavagga-aṭṭhakathā），
 * 只作 series 保留。作品的段落区间是
 * [paragraph, 同书下一条 book_titles.paragraph - 1]，且不超过它所在 level-1 节点的末尾
 * （book 209 vajirabuddhi-ṭīkā 一个 level-1 节点里有 5 部作品）。
 *
 * 分类来自 public/data/category/default.json，id 是按文件顺序深度优先的流水号，
 * 与 HomeController::flattenWithIds() 编号一致（/library/tipitaka/category/28 = 小部）。
 * 作品归入「包含它所在 level-1 节点全部 tag」的最深一级分类。
 *
 * 某 channel 是否收录某作品，看作品起点 (book, paragraph) 在 progress_chapters 里有没有该
 * channel 的行；该行的 progress 就是整部作品的进度。
 *
 * TODO: 给 progress_chapters 加 (book, para) 索引（只加索引，不改列；migration 由用户决定，2026-11-04 定为以后做）。
 *       现在按作品根节点查版本只能全表扫描（本地 12.6 万行）。2026-11-04 在事务里临时建索引实测（测完回滚），
 *       各页 SQL 合计：三藏首页 132→14ms、分类「经藏」292→19ms、分类末级 76→9ms、译本列表 67→13ms、
 *       作品详情 17→5ms。受影响的查询：本类的 publicChannelsQuery()、workChannels()，以及
 *       TipitakaController::versionsQuery()。
 *       顺带可考虑 pali_texts (level) 索引：buildWorks() 冷启动里 `where level = 1` 占 ~106ms（一天一次，不急）。
 */
class TipitakaCatalogService
{
    /**
     * 作品目录的缓存 key。分类 json 或 tag 有改动，等 TTL 过期，或手动 forget 这个 key。
     */
    public const CACHE_KEY = '/library/tipitaka/catalog/v2';

    private const CACHE_TTL = 86400;

    /** @var list<array{id: int, parent_id: int, name: string, tag: list<string>, level: int}>|null */
    private ?array $categories = null;

    /** @var list<array{sn: int, book: int, para: int, end: int, title: string, series: string, category_id: int|null, pcd_book_id: int|null, kind: string|null}>|null */
    private ?array $works = null;

    /** @var array<string, array<string, array>> channel uid => channelProgress() 的结果 */
    private array $progress = [];

    /** @var array<int, list<array>>|null parent_id => 子分类；分类树页对每个节点都要查，建一次索引 */
    private ?array $childrenIndex = null;

    /** @var array<int, list<int>> 分类 id => subtreeIds() 的结果 */
    private array $subtrees = [];

    /** @var array<int, list<array>> 分类 id => worksInCategory() 的结果 */
    private array $categoryWorks = [];

    /**
     * 封面渐变色池，按作品 sn 取余，保证同一作品颜色稳定。
     */
    private const COVER_GRADIENTS = [
        'linear-gradient(160deg, #2d1020, #ae6b8b)',
        'linear-gradient(160deg, #1a2d10, rgba(75, 114, 36, 0.61))',
        'linear-gradient(160deg, #0d1f3c, rgb(55, 98, 150))',
        'linear-gradient(160deg, #2d1020, rgb(151, 69, 94))',
        'linear-gradient(160deg, #1a1a2d, rgb(76, 68, 146))',
        'linear-gradient(160deg, #1a2820, rgb(55, 124, 99))',
    ];

    /**
     * channel 头像底色池，按 uid 取余，保证同一 channel 颜色稳定。
     */
    private const AVATAR_COLORS = ['#206bc4', '#7a3fc4', '#2fb344', '#c77700', '#d63939', '#0ca678', '#4263eb', '#ae3ec9'];

    public function __construct(protected TermService $termService) {}

    public static function channelColor(string $uid): string
    {
        return self::AVATAR_COLORS[hexdec(substr(md5($uid), 0, 4)) % count(self::AVATAR_COLORS)];
    }

    /**
     * 拍平的分类表，编号规则同 HomeController::flattenWithIds()。
     *
     * 不直接调那个静态方法：它的计数器是静态的，同一请求里调第二次编号就错位了。
     *
     * @return list<array{id: int, parent_id: int, name: string, tag: list<string>, level: int}>
     */
    public function categories(): array
    {
        if ($this->categories !== null) {
            return $this->categories;
        }

        $tree = json_decode(file_get_contents(public_path('data/category/default.json')), true);
        $flat = [];
        $nextId = 1;
        $walk = function (array $nodes, int $parentId, int $level) use (&$walk, &$flat, &$nextId): void {
            foreach ($nodes as $node) {
                $id = $nextId++;
                $flat[] = [
                    'id' => $id,
                    'parent_id' => $parentId,
                    'name' => $node['name'] ?? '',
                    'tag' => $node['tag'] ?? [],
                    'level' => $level,
                ];
                if (! empty($node['children']) && is_array($node['children'])) {
                    $walk($node['children'], $id, $level + 1);
                }
            }
        };
        $walk($tree, 0, 1);

        return $this->categories = $flat;
    }

    /**
     * @return array{id: int, parent_id: int, name: string, tag: list<string>, level: int}|null
     */
    public function category(int $id): ?array
    {
        // 编号是从 1 开始的连续流水号，下标 = id - 1
        $category = $this->categories()[$id - 1] ?? null;

        return $category && $category['id'] === $id ? $category : null;
    }

    /**
     * @return list<array{id: int, parent_id: int, name: string, tag: list<string>, level: int}>
     */
    public function children(int $parentId): array
    {
        if ($this->childrenIndex === null) {
            $this->childrenIndex = [];
            foreach ($this->categories() as $c) {
                $this->childrenIndex[$c['parent_id']][] = $c;
            }
        }

        return $this->childrenIndex[$parentId] ?? [];
    }

    /**
     * 从顶级到该分类的路径（含自身）。
     *
     * @return list<array{id: int, parent_id: int, name: string, tag: list<string>, level: int}>
     */
    public function categoryPath(int $id): array
    {
        $path = [];
        $current = $this->category($id);
        while ($current) {
            array_unshift($path, $current);
            $current = $current['parent_id'] ? $this->category($current['parent_id']) : null;
        }

        return $path;
    }

    /**
     * 该分类自身及全部后代的 id。
     *
     * @return list<int>
     */
    public function subtreeIds(int $id): array
    {
        if (isset($this->subtrees[$id])) {
            return $this->subtrees[$id];
        }
        $ids = [$id];
        foreach ($this->children($id) as $child) {
            $ids = array_merge($ids, $this->subtreeIds($child['id']));
        }

        return $this->subtrees[$id] = $ids;
    }

    /**
     * 分类的本地化名字：查术语表，查不到用巴利名。
     *
     * @param  list<array{name: string}>  $categories
     * @return array<string, string> 巴利名 => 显示名
     */
    public function localNames(array $categories, string $lang): array
    {
        $names = array_values(array_unique(array_filter(array_column($categories, 'name'))));
        $map = array_combine($names, $names) ?: [];
        foreach ($this->termService->glossaryByLemma($names, $lang) ?? [] as $term) {
            if (! empty($term->meaning)) {
                $map[$term->word] = $term->meaning;
            }
        }

        return $map;
    }

    /**
     * 全部作品，按 sn 排序。
     *
     * @return list<array{sn: int, book: int, para: int, end: int, title: string, series: string, category_id: int|null, pcd_book_id: int|null, kind: string|null}>
     */
    public function works(): array
    {
        if ($this->works !== null) {
            return $this->works;
        }

        return $this->works = RedisCache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->buildWorks());
    }

    /**
     * 按 sn 取作品。sn 有重复时（如 276）取 id 最小的那条。
     *
     * @return array{sn: int, book: int, para: int, end: int, title: string, series: string, category_id: int|null, pcd_book_id: int|null, kind: string|null}|null
     */
    public function work(int $sn): ?array
    {
        // FIXME: book_titles.sn 不唯一（276 同时是 214-3 与 214-2162），后一条按 sn 访问不到
        return collect($this->works())->firstWhere('sn', $sn);
    }

    /**
     * 段落所在的作品。
     *
     * @return array{sn: int, book: int, para: int, end: int, title: string, series: string, category_id: int|null, pcd_book_id: int|null, kind: string|null}|null
     */
    public function workAt(int $book, int $para): ?array
    {
        return collect($this->works())
            ->first(fn ($w) => $w['book'] === $book && $para >= $w['para'] && $para <= $w['end']);
    }

    /**
     * 分类（含后代）下的作品。
     *
     * @return list<array{sn: int, book: int, para: int, end: int, title: string, series: string, category_id: int|null, pcd_book_id: int|null, kind: string|null}>
     */
    public function worksInCategory(int $categoryId): array
    {
        if (isset($this->categoryWorks[$categoryId])) {
            return $this->categoryWorks[$categoryId];
        }
        $ids = array_flip($this->subtreeIds($categoryId));

        return $this->categoryWorks[$categoryId] = array_values(array_filter(
            $this->works(),
            fn ($w) => $w['category_id'] !== null && isset($ids[$w['category_id']])
        ));
    }

    /**
     * 某 channel 收录的作品及其进度，key 为 "book-para"（sn 有重复，不能当 key）。
     *
     * @return array<string, array{uid: string, progress: float, title: string|null, summary: string|null, updated_at: string|null}>
     */
    public function channelProgress(string $channelUid): array
    {
        return $this->progress[$channelUid] ??= $this->loadChannelProgress($channelUid);
    }

    /**
     * @return array<string, array{uid: string, progress: float, title: string|null, summary: string|null, updated_at: string|null}>
     */
    private function loadChannelProgress(string $channelUid): array
    {
        $roots = array_map(fn ($w) => [$w['book'], $w['para']], $this->works());
        if (empty($roots)) {
            return [];
        }

        return ProgressChapter::where('channel_id', $channelUid)
            ->whereIns(['book', 'para'], $roots)
            ->get(['uid', 'book', 'para', 'progress', 'title', 'summary', 'updated_at'])
            ->mapWithKeys(fn ($row) => ["{$row->book}-{$row->para}" => [
                'uid' => (string) $row->uid,
                'progress' => (float) $row->progress,
                'title' => $row->title,
                'summary' => $row->summary,
                'updated_at' => $row->updated_at?->toDateString(),
            ]])
            ->all();
    }

    /**
     * 公开 channel 的列表（分页），每个带收录作品数与最近更新时间，按作品数降序。
     *
     * 在 SQL 里分页：线上 channel 很多，不能全取出来再切。
     *
     * @return LengthAwarePaginator<int, array{uid: string, name: string, type: string, lang: string, owner_uid: string, works: int, updated_at: string|null}>
     */
    public function publicChannels(?string $lang = null, int $perPage = 20, ?string $type = null): LengthAwarePaginator
    {
        $paginator = $this->publicChannelsQuery()
            ->when($lang, fn ($q) => $q->where('c.lang', $lang))
            ->when($type, fn ($q) => $q->where('c.type', $type))
            ->groupBy('c.uid', 'c.name', 'c.type', 'c.lang', 'c.owner_uid')
            ->orderByRaw('count(distinct pc.uid) desc')
            ->orderBy('c.uid')
            ->select([
                'c.uid', 'c.name', 'c.type', 'c.lang', 'c.owner_uid',
                DB::raw('count(distinct pc.uid) as works'),
                DB::raw('max(pc.updated_at) as updated_at'),
            ])
            ->paginate($perPage);

        $paginator->setCollection($paginator->getCollection()->map(fn ($row) => [
            'uid' => (string) $row->uid,
            'name' => $row->name,
            'type' => $row->type,
            'lang' => $row->lang,
            'owner_uid' => (string) $row->owner_uid,
            'works' => (int) $row->works,
            'updated_at' => $row->updated_at ? substr($row->updated_at, 0, 10) : null,
        ]));

        return $paginator;
    }

    /**
     * 公开 channel 按某一列（lang / type）计数，给过滤项用。可再按另一列过滤，
     * 这样选了语言后，类型的数量是该语言下的数量（反之亦然）。
     *
     * @param  'lang'|'type'  $column
     * @param  array<'lang'|'type', string|null>  $filters
     * @return array<string, int> 值 => channel 数，按数量降序
     */
    public function publicChannelCounts(string $column, array $filters = []): array
    {
        return $this->publicChannelsQuery()
            ->when($filters['lang'] ?? null, fn ($q, $lang) => $q->where('c.lang', $lang))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('c.type', $type))
            ->groupBy("c.{$column}")
            ->orderByRaw('count(distinct c.uid) desc')
            ->selectRaw("c.{$column} as value, count(distinct c.uid) as n")
            ->pluck('n', 'value')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * 收录了作品（作品根节点有 progress_chapters 行）的公开 channel。
     *
     * TODO: 缺 progress_chapters (book, para) 索引，join book_titles 时全表扫描，见类注释。
     */
    private function publicChannelsQuery(): Builder
    {
        return DB::table('progress_chapters as pc')
            ->join('book_titles as b', function ($join) {
                $join->on('b.book', '=', 'pc.book')->on('b.paragraph', '=', 'pc.para');
            })
            ->join('channels as c', 'c.uid', '=', 'pc.channel_id')
            ->where('c.status', 30);
    }

    /**
     * channel 分组顺序；没列出的 type 排在后面。
     */
    private const TYPE_ORDER = ['translation', 'original', 'nissaya', 'commentary'];

    /**
     * 把 channel 行按 type 分组（作品版本页用）。
     *
     * @param  list<array{type: string}>  $channels
     * @return list<array{type: string, label: string, channels: list<array>}>
     */
    public function groupByType(array $channels): array
    {
        $rank = fn (string $type) => array_search($type, self::TYPE_ORDER, true) === false ? 99 : array_search($type, self::TYPE_ORDER, true);

        return collect($channels)
            ->groupBy('type')
            ->sortBy(fn ($items, $type) => $rank($type))
            ->map(fn ($items, $type) => ['type' => $type, 'label' => __('labels.'.$type), 'channels' => $items->values()->all()])
            ->values()
            ->all();
    }

    /**
     * 过滤项：「全部」+ 按数量降序的各值。$langFile 决定标签翻译（language / labels）。
     *
     * @param  array<string, int>  $counts  值 => 数量
     * @return list<array{value: string, label: string, count: int}>
     */
    public function filterOptions(array $counts, string $langFile = 'language'): array
    {
        arsort($counts);

        return collect($counts)
            ->map(fn ($count, $value) => ['value' => $value, 'label' => __("{$langFile}.{$value}"), 'count' => $count])
            ->values()
            ->prepend(['value' => 'all', 'label' => __('library.all'), 'count' => array_sum($counts)])
            ->all();
    }

    /**
     * 收录了该作品的其他公开 channel。
     *
     * @return list<array{uid: string, name: string, lang: string, type: string, owner: mixed, progress: int, updated_at: string|null}>
     */
    public function workChannels(array $work, ?string $exceptChannel = null): array
    {
        return ProgressChapter::with('channel.owner')
            ->where('book', $work['book'])
            ->where('para', $work['para'])
            ->when($exceptChannel, fn ($q) => $q->where('channel_id', '<>', $exceptChannel))
            ->whereHas('channel', fn ($q) => $q->where('status', 30))
            ->orderByDesc('progress')
            ->get()
            ->map(fn ($row) => [
                'uid' => (string) $row->channel_id,
                'name' => $row->channel->name,
                'lang' => $row->channel->lang,
                'type' => $row->channel->type,
                'owner' => $row->channel->owner,
                'progress' => (int) round($row->progress * 100),
                'updated_at' => $row->updated_at?->toDateString(),
            ])
            ->all();
    }

    /**
     * channel 的展示信息；不存在返回 null。
     *
     * 与阅读页一致，不限 status：用户自己的私有 channel 也能从 dashboard 点进来看。
     */
    public function channel(string $uid): ?Channel
    {
        return Channel::with('owner')->where('uid', $uid)->first();
    }

    /**
     * 作品的封面图。目前一律返回 null，页面用渐变色 + 书名作封面（coverGradient()）。
     *
     * TODO: 以后从 S3 取封面（2026-11-06 定）。原来按 pcd_book_id 找本地
     *       public/assets/images/cover/zh-hans/1/{id}.png，已停用；$work['pcd_book_id'] 仍在目录里，可作 S3 的 key。
     */
    public function coverUrl(array $work): ?string
    {
        return null;
    }

    public function coverGradient(array $work): string
    {
        return self::COVER_GRADIENTS[$work['sn'] % count(self::COVER_GRADIENTS)];
    }

    /**
     * 书卡片（x-ui.card-book）的数据：某 channel 的某部作品。
     *
     * @param  array{uid: string, name: string, type: string, lang: string, owner?: mixed}  $channel
     * @param  array{progress: float, title: string|null, summary?: string|null}|null  $progress  progress_chapters 那一行
     * @return array<string, mixed>
     */
    public function bookCard(array $work, array $channel, ?array $progress): array
    {
        $title = ! empty($progress['title']) ? $progress['title'] : $work['title'];

        return [
            'id' => $work['sn'],
            'url' => route('library.tipitaka.channels.work', ['channel' => $channel['uid'], 'sn' => $work['sn']]),
            'title' => $title,
            'author' => $channel['name'],
            'publisher' => $channel['owner'] ?? null,
            'subTitle' => $work['kind'] ? __('library.'.$work['kind']) : null,
            'type' => __('labels.'.$channel['type']),
            'language' => __('language.'.$channel['lang']),
            'cover' => $this->coverUrl($work),
            'cover_gradient' => $this->coverGradient($work),
            'description' => $progress['summary'] ?? '',
            'progress' => $progress ? (int) round($progress['progress'] * 100) : null,
        ];
    }

    /**
     * @return list<array{sn: int, book: int, para: int, end: int, title: string, series: string, category_id: int|null, pcd_book_id: int|null, kind: string|null}>
     */
    private function buildWorks(): array
    {
        $level1 = PaliText::where('level', 1)
            ->orderBy('book')->orderBy('paragraph')
            ->get(['uid', 'book', 'paragraph', 'chapter_len', 'pcd_book_id']);

        // level-1 节点 uid => tag 名集合
        $tagsByUid = TagMap::query()
            ->join('tags', 'tags.id', '=', 'tag_maps.tag_id')
            ->whereIn('tag_maps.anchor_id', $level1->pluck('uid'))
            ->get(['tag_maps.anchor_id', 'tags.name'])
            ->groupBy('anchor_id')
            ->map(fn ($rows) => $rows->pluck('name')->all());

        // 深的分类排前面，下面取第一个命中的即为最深一级
        $categories = collect($this->categories())->filter(fn ($c) => ! empty($c['tag']))->sortByDesc('level')->values()->all();
        $titles = BookTitle::orderBy('sn')->orderBy('id')->get(['sn', 'book', 'paragraph', 'title']);

        // 先按书建索引：在 Eloquent 集合上逐条 where() 是 O(n²)，冷启动要 3 秒
        $level1ByBook = [];
        foreach ($level1 as $node) {
            $level1ByBook[(int) $node->book][] = $node;
        }
        $titleParasByBook = [];
        foreach ($titles as $t) {
            $titleParasByBook[(int) $t->book][] = (int) $t->paragraph;
        }
        // 作品起点段落的目录标题 = 作品名
        $tocs = PaliText::query()
            ->whereIns(['book', 'paragraph'], $titles->map(fn ($t) => [$t->book, $t->paragraph])->all())
            ->get(['book', 'paragraph', 'toc'])
            ->mapWithKeys(fn ($p) => ["{$p->book}-{$p->paragraph}" => trim((string) $p->toc)]);

        $works = [];
        foreach ($titles as $title) {
            $book = (int) $title->book;
            $para = (int) $title->paragraph;

            // 所在 level-1 节点：同书里起点 ≤ 作品起点的最后一个（已按段落升序）
            $top = null;
            foreach ($level1ByBook[$book] ?? [] as $node) {
                if ($node->paragraph <= $para) {
                    $top = $node;
                }
            }
            $end = $top ? $top->paragraph + $top->chapter_len - 1 : $para;
            $next = array_filter($titleParasByBook[$book] ?? [], fn ($p) => $p > $para);
            if ($next) {
                $end = min($end, min($next) - 1);
            }

            $tags = $top ? ($tagsByUid[(string) $top->uid] ?? []) : [];
            $best = null;
            if ($tags) {
                foreach ($categories as $category) {
                    if (empty(array_diff($category['tag'], $tags))) {
                        $best = $category;
                        break;
                    }
                }
            }
            $kind = collect(['pāḷi', 'aṭṭhakathā', 'ṭīkā'])->first(fn ($k) => in_array($k, $tags, true));

            $works[] = [
                'sn' => (int) $title->sn,
                'book' => $book,
                'para' => $para,
                'end' => (int) $end,
                'title' => ($tocs["{$book}-{$para}"] ?? '') ?: trim($title->title),
                'series' => trim($title->title),
                'category_id' => $best['id'] ?? null,
                'pcd_book_id' => $top?->pcd_book_id ? (int) $top->pcd_book_id : null,
                'kind' => $kind,
            ];
        }

        return $works;
    }
}
