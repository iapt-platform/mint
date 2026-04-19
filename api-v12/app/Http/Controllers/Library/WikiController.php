<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\WikiContentParser;
use App\Services\TermService;

class WikiController extends Controller
{
    public function __construct(
        private TermService    $termService
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

    // ── Actions ──────────────────────────────────────────────────

    public function index(string $lang)
    {
        $result = $this->termService->communityTerms($lang);
        $fakeRequest = Request::create('', 'GET', []);
        $termResource = $result['data'];
        $terms    = $termResource->toArray($fakeRequest);

        $first = $terms[0];
        $today = [
            'word'      => $first['word'],
            'lang'      => $first['language'],
            'slug'      => $first['word'],
            'meaning'        => $first['meaning'],
            'quality'   => 'featured',   // featured | stub | review | null
            'category'  => '法义术语',
            'content' => $first['summary']
        ];


        return view('library.wiki.index', [
            'today'         => $today,
            'featured'      => $this->featured($terms),
            'stats'         => $this->mockStats(),
            'recentUpdates' => $this->mockRecentUpdates(),
            'categories'    => $this->categories(),
            'lang' => $lang
        ]);
    }

    public function show(string $lang, string $word)
    {

        $term = $this->termService->communityTerm($word, $lang);
        $urlParam = ['format' => 'html'];
        $fakeRequest = Request::create('', 'GET', $urlParam);
        $termArray    = $term->toArray($fakeRequest);
        $entry = [
            'word'      => $termArray['word'],
            'lang'      => $termArray['language'],
            'slug'      => $termArray['word'],
            'meaning'        => $termArray['meaning'],
            'quality'   => 'featured',   // featured | stub | review | null
            'category'  => '法义术语',
            'tags'      => [],
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
            'content' => $termArray['html']
        ];
        $parsed  = WikiContentParser::parse($entry['content']);

        return view('library.wiki.show', [
            'entry' => array_merge($entry, [
                'content' => $parsed['content'],
                'toc'     => $parsed['toc'],
            ]),
            'categories' => $this->categories(),
            'lang' => $lang
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function categories(): array
    {
        return [
            ['slug' => 'all',      'label' => '全部'],
            ['slug' => 'term',     'label' => '法义术语'],
            ['slug' => 'person',   'label' => '人物传记'],
            ['slug' => 'text',     'label' => '经典文献'],
            ['slug' => 'school',   'label' => '宗派历史'],
            ['slug' => 'practice', 'label' => '修行方法'],
            ['slug' => 'place',    'label' => '佛教地理'],
        ];
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
}
