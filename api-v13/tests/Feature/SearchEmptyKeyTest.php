<?php

/**
 * 空 key 必须被 SearchRequest 拦下。
 *
 * 修复前它不报错，而是匹配到「空值」那一类数据：wbw 检索 whereIn('real', [''])
 * 命中 49 万个段落，标题检索 like '%%' 命中全部三万多条。调用方看到的是成功的
 * 响应和满屏结果，却与查询无关——AI agent 曾据此把整个语料库当成命中。
 *
 * 校验在进控制器之前就失败，所以这些用例不需要造数据。
 */
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

$emptyKeys = [
    '空字符串' => '',
    '只有空白' => '   ',
    '只有逗号' => ',,',
    '只有分号' => ';;',
];

$endpoints = [
    'search view=title' => '/api/v2/search?view=title',
    'search view=page' => '/api/v2/search?view=page',
    'search view=pali' => '/api/v2/search?view=pali',
    'search-book-list' => '/api/v2/search-book-list',
    'search-pali-wbw' => '/api/v2/search-pali-wbw',
    'search-pali-wbw-books' => '/api/v2/search-pali-wbw-books',
];

foreach ($endpoints as $name => $url) {
    $glue = str_contains($url, '?') ? '&' : '?';

    foreach ($emptyKeys as $label => $key) {
        it("rejects {$label} on {$name}", function () use ($url, $glue, $key) {
            $this->getJson($url.$glue.'key='.urlencode($key))
                ->assertStatus(422)
                ->assertJsonValidationErrors('key');
        });
    }

    it("rejects a missing key on {$name}", function () use ($url) {
        $this->getJson($url)
            ->assertStatus(422)
            ->assertJsonValidationErrors('key');
    });
}

it('drops empty words split out of a valid key', function () {
    // `dhammo,,` 能过校验——它确实有一个可检索的词。但 explode 切出的空串若进了
    // whereIn('real', ...)，就会把 real 为空的段落一并捞出来；线上那批有 49 万个
    $row = fn (int $paragraph, string $real, string $word) => [
        'book' => 1, 'paragraph' => $paragraph, 'wid' => 1,
        'word' => $word, 'real' => $real,
        'type' => '', 'gramma' => '', 'part' => '', 'style' => '',
        'pcd_book_id' => 1, 'weight' => 1,
    ];
    DB::table('wbw_templates')->insert([
        $row(1, 'dhammo', 'dhammo'),
        $row(2, '', '.'),   // 线上这类空词元有四百多万行
    ]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key='.urlencode('dhammo,,'))
        ->assertOk();

    expect($response->json('data.count'))->toBe(1)
        ->and($response->json('data.rows.0.paragraph'))->toBe(1);
});

it('rejects an empty key without an Accept header too', function () {
    // 默认配置下校验失败会 302 跳首页，客户端跟随重定向就拿到一张 HTML 首页；
    // bootstrap/app.php 里的 shouldRenderJsonWhen 让 api/* 一律回 JSON
    $this->get('/api/v2/search?view=title&key=')
        ->assertStatus(422)
        ->assertJsonValidationErrors('key');
});
