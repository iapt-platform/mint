<?php

namespace App\Http\Controllers\Library;

use App\Helpers\WikiContentParser;
use App\Http\Controllers\Controller;
use App\Services\TermService;
use App\Services\WikiService;
use Illuminate\Http\Request;

/**
 * library 百科页。词条数据直接查库（{@see WikiService}），不经 OpenSearch。
 */
class WikiController extends Controller
{
    public function __construct(
        private TermService $termService,
        private WikiService $wiki,
    ) {}

    // ── Mock 数据 ────────────────────────────────────────────────

    private function mockEntries(): array
    {
        return [
            [
                'word' => 'Anicca',
                'lang' => 'zh-Hans',
                'slug' => 'anicca',
                'meaning' => '无常',
                'quality' => 'featured',   // featured | stub | review | null
                'category' => '法义术语',
                'tags' => ['三相', '法义术语', '相应部', '内观', '五蕴'],
                'langs' => [
                    ['lang' => 'zh-Hant', 'label' => '繁体中文',   'word' => '无常'],
                    ['lang' => 'en', 'label' => 'English', 'word' => 'Impermanence'],
                ],
                'related' => [
                    ['word' => 'Dukkha',    'zh' => '苦',   'lang' => 'pi'],
                    ['word' => 'Anattā',    'zh' => '无我', 'lang' => 'pi'],
                    ['word' => 'Vipassanā', 'zh' => '内观', 'lang' => 'pi'],
                    ['word' => 'Ti-lakkhaṇa', 'zh' => '三相', 'lang' => 'pi'],
                ],
                'content' => <<<'HTML'
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
        $subs = null;

        if ($category) {
            $taxNode = collect(config('taxonomy'))->firstWhere('id', $category);
            // 不是分类树里的一级 id，就当作分类名（词条正文里的 {{category|…}} 链过来的）
            $subs = $taxNode
                ? $this->subEntries($taxNode['subs'], $lang, $quality)
                : [[
                    'id' => $category,
                    'label' => $category,
                    'tags' => [],
                    'entries' => $this->wiki->categoryEntries([$category], $lang, $quality),
                ]];
        }

        $result = $this->termService->communityTerms($lang);
        $fakeRequest = Request::create('', 'GET', []);
        $terms = $result['data']->toArray($fakeRequest);
        // 该语言的社区 channel 不存在或没有数据时不显示「今日词条」（原来直接取 [0]，会 500）
        $first = $terms[0] ?? null;

        $today = $first ? [
            'word' => $first['word'],
            'lang' => $first['language'],
            'slug' => $first['word'],
            'meaning' => $first['meaning'],
            'quality' => 'featured',
            'category' => '法义术语',
            'content' => $first['summary'],
        ] : null;

        return response()
            ->view('library.wiki.index', [
                'today' => $request->has('category') ? null : $today,
                'featured' => $category ? null : $this->featured($terms),
                'stats' => $this->mockStats(),
                'recentUpdates' => $this->mockRecentUpdates(),
                'categories' => $this->categories(),
                'lang' => $lang,
                'category' => $category,
                'subs' => $subs,   // null | array of subs with entries
                'quality' => $quality,
                'qualities' => $this->qualities(),
            ])->withCookie($cookie);
    }

    /**
     * 给每个二级分类注入词条列表：分类名为二级分类本身或它下面的任一标签。
     */
    private function subEntries(array $subs, string $lang, string $quality): array
    {
        return array_map(function ($sub) use ($lang, $quality) {
            $entries = $this->wiki->categoryEntries([$sub['label'], ...$sub['tags']], $lang, $quality);

            return array_merge($sub, ['entries' => $entries]);
        }, $subs);
    }

    /**
     * 词条详情。$word 是词或 guid；词条设了重定向（同一词条的另一种拼写）就转到目标词条。
     */
    public function show(string $lang, string $word)
    {
        $term = $this->wiki->find($lang, $word);
        if (! $term) {
            abort(404);
        }
        $target = $this->wiki->redirectTarget($term);
        if ($target) {
            return redirect()->route('library.wiki.show', [$lang, $target->guid]);
        }

        $cats = $this->wiki->categoriesOf($term);
        $entry = [
            'word' => $term->word,
            'lang' => $term->language,
            'slug' => $term->word,
            'meaning' => $term->meaning,
            'quality' => $term->quality,   // featured | standard | draft | pending | null
            'category' => '法义术语',
            'tags' => $cats,
            'langs' => [
                ['lang' => 'zh-Hant', 'label' => '繁体中文',   'word' => '无常'],
                ['lang' => 'en', 'label' => 'English', 'word' => 'Impermanence'],
            ],
            'related' => $this->wiki->related($term, $cats),
            'content' => $this->wiki->contentHtml($term, $lang),
        ];
        $parsed = WikiContentParser::parse($entry['content']);

        return view('library.wiki.show', [
            'entry' => array_merge($entry, [
                'content' => $parsed['content'],
                'toc' => $parsed['toc'],
                'edit_url' => config('mint.server.dashboard_base_path')."/workspace/term/{$term->guid}/edit",
                'zh' => '编辑',
                'other_versions' => $this->wiki->otherVersions($term),
            ]),
            'categories' => $this->categories(),
            'lang' => $lang,

        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function categories(): array
    {
        $cats = collect(config('taxonomy'))
            ->map(fn ($cat) => ['id' => $cat['id'], 'label' => $cat['label']])
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
            'total_articles' => 2847,
            'total_terms' => 1256,
            'languages_count' => 10,
            'contributors' => 328,
            'today_updates' => 12,
        ];

        // Mock 热门搜索标签
        $hotTags = ['无常', 'Anicca', '四圣谛', '涅槃', '内观', '业力', '慈悲', '般若'];

        // Mock 每日一词（可选展示）
        $dailyTerm = [
            'word' => 'Dhammapada',
            'zh' => '法句经',
            'lang' => 'pi',
            'meaning' => '佛陀的偈颂集，佛教最重要的经典之一',
        ];

        return view('library.wiki.home', [
            'languages' => $languages,
            'currentLang' => $lang,
            'stats' => $stats,
            'hotTags' => $hotTags,
            'dailyTerm' => $dailyTerm,
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
            'total' => 2847,
            'this_month' => 43,
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
}
