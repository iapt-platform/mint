<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\WikiContentParser;
use App\Services\TermService;
use Illuminate\Support\Str;
use App\Services\OpenSearchService;


class WikiController extends Controller
{
    // 质量等级（数值越小等级越高）
    private  $qualityRank = [
        'featured' => 1,
        'standard' => 2,
        'draft'    => 3,
        'pending'  => 4,
    ];

    public function __construct(
        private TermService    $termService,
        private OpenSearchService    $searchService
    ) {}

    // ── Mock 数据 ────────────────────────────────────────────────

    private function mockEntries(): array
    {
        return [
            [
                'word'      => 'Anicca',
                'lang'      => 'zh-Hans',
                'slug'      => 'anicca',
                'meaning'        => '无常',
                'quality'   => 'featured',   // featured | stub | review | null
                'category'  => '法义术语',
                'tags'      => ['三相', '法义术语', '相应部', '内观', '五蕴'],
                'langs'     => [
                    ['lang' => 'zh-Hant', 'label' => '繁体中文',   'word' => '无常'],
                    ['lang' => 'en', 'label' => 'English', 'word' => 'Impermanence'],
                ],
                'related' => [
                    ['word' => 'Dukkha',    'zh' => '苦',   'lang' => 'pi'],
                    ['word' => 'Anattā',    'zh' => '无我', 'lang' => 'pi'],
                    ['word' => 'Vipassanā', 'zh' => '内观', 'lang' => 'pi'],
                    ['word' => 'Ti-lakkhaṇa', 'zh' => '三相', 'lang' => 'pi'],
                ],
                'content' => <<<HTML
<h2>词源与释义</h2>

<blockquote>
    「诸比丘，色是无常的。无常的即是苦的。苦的即是无我的。无我的即应如实以正慧观察……」
    <cite>SN 22.15 · Khandha-saṃyutta · 相应部·蕴相应</cite>
</blockquote>

HTML,
            ],
        ];
    }



    // ── Actions ──────────────────────────────────────────────────

    public function index(Request $request, string $lang)
    {
        $quality = $request->input('quality')
            ?? $request->cookie('wiki_quality_filter')
            ?? 'draft';

        $cookie = cookie()->forever('wiki_quality_filter', $quality);

        $category = $request->input('category');
        $subs     = null;

        if ($category) {
            $taxNode = collect(config('taxonomy'))->firstWhere('id', $category);
            $subs    = $taxNode ? $this->subEntries($taxNode['subs'], $lang, $quality) : null;
        }

        $result      = $this->termService->communityTerms($lang);
        $fakeRequest = Request::create('', 'GET', []);
        $terms       = $result['data']->toArray($fakeRequest);
        $first       = $terms[0];

        $today = [
            'word'     => $first['word'],
            'lang'     => $first['language'],
            'slug'     => $first['word'],
            'meaning'  => $first['meaning'],
            'quality'  => 'featured',
            'category' => '法义术语',
            'content'  => $first['summary'],
        ];



        return response()
            ->view('library.wiki.index', [
                'today'         => $request->has('category') ? null : $today,
                'featured'      => $category ? null : $this->featured($terms),
                'stats'         => $this->mockStats(),
                'recentUpdates' => $this->mockRecentUpdates(),
                'categories'    => $this->categories(),
                'lang'          => $lang,
                'category'      => $category,
                'subs'          => $subs,   // null | array of subs with entries
                'quality'   => $quality,
                'qualities' => $this->qualities(),
            ])->withCookie($cookie);
    }

    /**
     * 给每个二级分类注入 词条列表
     * 真实数据替换时：按 sub['tags'] 查询术语表，返回同结构数组即可
     */
    private function subEntries(array $subs, string $lang, string $quality): array
    {
        return array_map(function ($sub) use ($lang, $quality) {
            $entries = $this->querySubCat($sub['tags'], $lang, $quality);
            return array_merge($sub, ['entries' => $entries]);
        }, $subs);
    }

    private function querySubCat(array $cats, string $lang, string $quality): array
    {
        $params = [
            'pageSize'     => 1000,
            'resourceType' => 'term',
            'language'     => $lang,
            'tags'         => array_map(fn($n) => "category:{$n}", $cats),
        ];

        $result = $this->searchService->search($params);



        // 当前允许的最大等级
        $maxRank = $this->qualityRank[$quality] ?? 4;

        $unique = [];

        foreach ($result['hits']['hits'] as $item) {
            $text = $item['_source']['title']['text'];
            $id   = $item['_source']['resource_id'];

            if (isset($item['_source']['tags'])) {
                $itemQuality = $this->getQuality($item['_source']['tags']) ?? 'pending';
            } else {
                $itemQuality = 'pending';
            }

            $itemRank = $this->qualityRank[$itemQuality] ?? 4;

            // 按输入质量过滤
            if ($itemRank > $maxRank) {
                continue;
            }

            $record = [
                'id'      => $id,
                'word'    => $text['pali'],
                'zh'      => $text['zh'],
                'quality' => $itemQuality,
            ];

            // 用 pali + zh 去重
            $key = mb_strtolower(trim($text['pali']) . '|' . trim($text['zh']));

            // 如果不存在，直接保存
            if (!isset($unique[$key])) {
                $unique[$key] = $record;
                continue;
            }

            // 已存在时，保留质量更高的
            $existingQuality = $unique[$key]['quality'];
            $existingRank    = $this->qualityRank[$existingQuality] ?? 4;

            if ($itemRank < $existingRank) {
                $unique[$key] = $record;
            }
        }

        return array_values($unique);
    }

    private function getQuality(array $tags)
    {
        $qualityTag = array_find($tags, function ($tag) {
            return str_contains($tag, 'quality:');
        });
        if ($qualityTag) {
            return mb_substr($qualityTag, 8, null, "UTF-8");
        } else {
            return null;
        }
    }
    private function getCategories(array $tags)
    {
        $catTag = array_filter($tags, function ($tag) {
            return str_contains($tag, 'category:');
        });
        return array_map(fn($item) => mb_substr($item, mb_strlen('category:', 'UTF-8')), $catTag);
    }


    public function show(string $lang, string $word)
    {
        if (Str::isUuid($word)) {
            $term = $this->termService->find($word, 'html');
        } else {
            $term = $this->termService->communityWiki($word, $lang, 'html');
        }

        try {
            $result = $this->searchService->get("term_{$term['guid']}");
        } catch (\Throwable $th) {
            abort(404);
        }

        if (isset($result['_source']['tags'])) {
            $quality = $this->getQuality($result['_source']['tags']);
        } else {
            $quality = null;
        }

        $cats = $this->getCategories($result['_source']['tags']);
        $entry = [
            'word'      => $term['word'],
            'lang'      => $term['language'],
            'slug'      => $term['word'],
            'meaning'        => $term['meaning'],
            'quality'   => $quality,   // featured | standard | draft | pending | null
            'category'  => '法义术语',
            'tags'      => $cats,
            'langs'     => [
                ['lang' => 'zh-Hant', 'label' => '繁体中文',   'word' => '无常'],
                ['lang' => 'en', 'label' => 'English', 'word' => 'Impermanence'],
            ],
            'related' => $this->related($cats),
            'content' => $term['html'] ?? ''
        ];
        $parsed  = WikiContentParser::parse($entry['content']);

        return view('library.wiki.show', [
            'entry' => array_merge($entry, [
                'content' => $parsed['content'],
                'toc'     => $parsed['toc'],
                'edit_url' => config('mint.server.dashboard_base_path') . "/workspace/term/{$term['guid']}/edit",
                'zh' => '编辑',
                'other_versions' => $this->otherVersions($term['word']),
            ]),
            'categories' => $this->categories(),
            'lang' => $lang,

        ]);
    }

    private function related(array $tags): array
    {
        $params = [
            'pageSize'     => 5,
            'resourceType' => 'term',
            'tags'         => array_map(fn($n) => "category:{$n}", $tags),
        ];

        $result = $this->searchService->search($params);
        $relates = [];

        foreach ($result['hits']['hits'] as $item) {
            $relates[] = [
                'word' => $item['_source']['title']['text']['pali'],
                'zh' => $item['_source']['title']['text']['zh'],
                'lang' => $item['_source']['language']
            ];
        }
        return $relates;
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function categories(): array
    {
        $cats = collect(config('taxonomy'))
            ->map(fn($cat) => ['id' => $cat['id'], 'label' => $cat['label']])
            ->toArray();

        return $cats;
    }

    // 在 WikiController.php 中添加此方法

    // 在 WikiController.php 中添加
    public function home(string $lang = 'zh-Hans')
    {
        // Mock 语言列表
        $languages = [
            ['code' => 'zh-Hans', 'name' => '简体中文'],
            ['code' => 'zh-Hant', 'name' => '繁體中文'],
            ['code' => 'en',       'name' => 'English'],
            ['code' => 'ja',       'name' => '日本語'],
            ['code' => 'ko',       'name' => '한국어'],
            ['code' => 'vi',       'name' => 'Tiếng Việt'],
            ['code' => 'my',       'name' => 'မြန်မာစာ'],
        ];

        // Mock 统计数据
        $stats = [
            'total_articles'   => 2847,
            'total_terms'      => 1256,
            'languages_count'  => 10,
            'contributors'     => 328,
            'today_updates'    => 12,
        ];

        // Mock 热门搜索标签
        $hotTags = ['无常', 'Anicca', '四圣谛', '涅槃', '内观', '业力', '慈悲', '般若'];

        // Mock 每日一词（可选展示）
        $dailyTerm = [
            'word'      => 'Dhammapada',
            'zh'        => '法句经',
            'lang'      => 'pi',
            'meaning'   => '佛陀的偈颂集，佛教最重要的经典之一',
        ];

        return view('library.wiki.home', [
            'languages'     => $languages,
            'currentLang'   => $lang,
            'stats'         => $stats,
            'hotTags'       => $hotTags,
            'dailyTerm'     => $dailyTerm,
        ]);
    }


    private function qualities(): array
    {
        return [
            ['value' => 'featured', 'label' => '典范条目', 'subtitle' => '平台推荐'],
            ['value' => 'standard',  'label' => '规范条目', 'subtitle' => '邀请试读'],
            ['value' => 'draft',     'label' => '草稿', 'subtitle' => '仅供参考'],
            ['value' => 'pending',   'label' => '待定', 'subtitle' => '审稿专用'],
        ];
    }

    private function featured(array $all): array
    {
        return array_map(function ($item) {
            return ['word' => $item['word'],     'zh' => $item['meaning'],   'lang' => $item['language'], 'category' => '法义术语'];
        }, $all);
    }

    private function mockStats(): array
    {
        return [
            'total'        => 2847,
            'this_month'   => 43,
            'contributors' => 128,
        ];
    }

    private function mockRecentUpdates(): array
    {
        return [
            ['word' => 'Nibbāna',  'lang' => 'pi'],
            ['word' => '四圣谛',   'lang' => 'zh'],
            ['word' => '阿含经',   'lang' => 'zh'],
            ['word' => 'Rājagaha', 'lang' => 'pi'],
        ];
    }


    private function otherVersions(string $word): array
    {
        $params = [
            'query' => $word,
            'pageSize'     => 10,
            'resourceType' => 'term',
            'tags'         => array_map(fn($n) => "quality:{$n}", array_keys($this->qualityRank)),
        ];

        $result = $this->searchService->search($params);
        $versions = [];

        foreach ($result['hits']['hits'] as $item) {
            $text = $item['_source']['title']['text'];
            $id   = $item['_source']['resource_id'];
            $versions[] = [
                'type'     => 'term',
                'id'       => $id,
                'lang'     => $item['_source']['language'],
                'title'    => $text['zh'] ?? 'null',
                'quality'  => $this->getQuality($item['_source']['tags'] ?? 'quality:pending'),
                'category' => '法義術語',
                'snippet'  => $item['_source']['summary']['text'],
                'updated'  => $item['_source']['updated_at'],
            ];
        }
        return $versions;
    }
}
