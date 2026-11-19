<?php

use App\Models\Article;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/*
 * staging 日志（2026-11-19）里两类必然 500 的 web 页面的回归测试：
 * - blog 的子页面指向被注释掉的 BlogController 方法 → BadMethodCallException
 * - 文集阅读页没给 book.read 视图 book_title → Undefined array key
 */

uses(RefreshDatabase::class);

it('answers 404 instead of 500 for blog pages that are not implemented yet', function (string $path) {
    makeStudio('blogger');

    $this->get("/blog/blogger/{$path}")->assertNotFound();
})->with([
    'categories' => 'categories',
    'archives' => 'archives',
    'archives by year' => 'archives/2024',
    'tag' => 'tag/dukkha',
    'search' => 'search?q=dukkha',
    'post' => 'some-post',
]);

it('still renders the blog home, whose layout links to those routes by name', function () {
    makeStudio('blogger');

    $this->get('/blog/blogger')
        ->assertSuccessful()
        ->assertSee(route('blog.archives', ['user' => 'blogger'], false))
        ->assertSee(route('blog.search', ['user' => 'blogger'], false));
});

it('renders an anthology article with the anthology title as the book title', function () {
    $owner = makeStudio('editor');
    // ArticleResource 渲染时取作者名下第一个译文 channel，没有就报错（v2 代码的另一个问题，不在此修）
    makeChannel($owner, 'editor translation');
    $articleId = (string) Str::uuid();
    $anthologyId = (string) Str::uuid();
    $now = time() * 1000;

    (new Article)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => $articleId,
        'title' => '第一单元',
        'content' => '正文',
        'owner' => $owner,
        'owner_id' => 0,
        'editor_id' => 0,
        'status' => 30,
        'create_time' => $now,
        'modify_time' => $now,
    ])->save();
    (new Collection)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => $anthologyId,
        'title' => '初级班课本',
        'article_list' => json_encode([['article_id' => $articleId, 'level' => 1, 'title' => '第一单元']]),
        'owner' => $owner,
        'owner_id' => 0,
        'editor_id' => 0,
        'status' => 30,
        'create_time' => $now,
        'modify_time' => $now,
    ])->save();

    $this->get("/library/anthology/{$anthologyId}/read/{$articleId}")
        ->assertSuccessful()
        ->assertSee('<h5 class="offcanvas-title">初级班课本</h5>', false);
});
