<?php

use App\Models\BookTitle;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\TermCategory;
use App\Services\OpenSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * library 百科页（/library/wiki/*）直接查库：分类取 term_categories，质量取 dhamma_terms.quality。
 * OpenSearch 换成一调用就失败的替身，确保这些页面不再依赖它。
 */

beforeEach(function () {
    $this->app->instance(OpenSearchService::class, Mockery::mock(OpenSearchService::class, function ($mock) {
        $mock->shouldReceive('search', 'get')->never();
    }));
});

/** 直接写一条术语（带分类），返回 guid */
function wikiTerm(string $channel, string $word, array $attrs = [], array $categories = []): string
{
    $guid = (string) Str::uuid();
    (new DhammaTerm)->forceFill($attrs + [
        'id' => random_int(1, PHP_INT_MAX),
        'guid' => $guid,
        'word' => $word,
        'word_en' => $word,
        'meaning' => "{$word}-义",
        'note' => "{$word} 的正文。",
        'channal' => $channel,
        'language' => 'zh-Hans',
        'owner' => Channel::where('uid', $channel)->value('owner_uid'),
        'editor_id' => 0,
        'content_type' => 'markdown-e2',
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();
    foreach ($categories as $i => $category) {
        TermCategory::create(['term_guid' => $guid, 'category' => $category, 'position' => $i]);
    }

    return $guid;
}

it('lists the terms of a category and its tags, filtered by quality, public channels only', function () {
    $owner = makeStudio('owner');
    $public = makeChannel($owner, 'public');
    $private = makeChannel($owner, 'private');
    Channel::where('uid', $private)->update(['status' => Channel::STATUS_PRIVATE]);
    wikiTerm($public, 'by-sub-label', ['quality' => 'standard'], ['戒条罪类']);
    wikiTerm($public, 'by-tag', ['quality' => 'draft'], ['学处']);
    wikiTerm($public, 'pending-one', [], ['学处']);
    wikiTerm($private, 'zzprivateterm', ['quality' => 'featured'], ['学处']);
    wikiTerm($public, 'zzothercategory', ['quality' => 'featured'], ['禅修']);

    $html = $this->get('/library/wiki/zh-Hans?category=vinaya&quality=pending')->assertOk()->getContent();
    expect($html)->toContain('by-sub-label')->toContain('by-tag')->toContain('pending-one')
        ->not->toContain('zzprivateterm')->not->toContain('zzothercategory');

    $html = $this->get('/library/wiki/zh-Hans?category=vinaya&quality=draft')->assertOk()->getContent();
    expect($html)->toContain('by-sub-label')->toContain('by-tag')->not->toContain('pending-one');
});

it('shows a term by word or id with its categories and rendered note', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $guid = wikiTerm($channel, 'dhamma', ['quality' => 'featured', 'summary' => '一句话摘要。', 'note' => "## 小节\n\n正文段落。"], ['法义']);
    wikiTerm($channel, 'sīla', [], ['法义']);

    foreach (['dhamma', $guid] as $key) {
        $html = $this->get("/library/wiki/zh-Hans/{$key}")->assertOk()->getContent();
        expect($html)->toContain('一句话摘要。')->toContain('小节')->toContain('正文段落。')->toContain('法义')
            ->toContain('sīla'); // 相关条目：共享分类
    }
});

it('renders categories in the note as links to the category list, and lists terms by category name', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $guid = wikiTerm($channel, 'vinaya', ['note' => "正文。\n\n{{category|律学}} {{category|学处}}", 'quality' => 'standard'], ['律学', '学处']);
    wikiTerm($channel, 'zzothercat', ['quality' => 'standard'], ['禅修']);

    $html = $this->get("/library/wiki/zh-Hans/{$guid}")->assertOk()->getContent();
    $url = e(route('library.wiki.index', ['lang' => 'zh-Hans', 'category' => '学处']));
    expect($html)->toContain('<a class="tpl-category" href="'.$url.'">学处</a>')
        ->not->toContain('class="wiki-tags"');

    // 分类名（不是分类树里的 id）也能列出词条
    $list = $this->get('/library/wiki/zh-Hans?category='.urlencode('学处').'&quality=pending')->assertOk()->getContent();
    expect($list)->toContain('vinaya')->not->toContain('zzothercat');
});

it('puts each reference of {{ref}} on its own line', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    // 文献名取自 book_titles：两处引用落在两本书里，各成一条
    BookTitle::query()->insert([
        ['book' => 207, 'paragraph' => 1, 'title' => 'kaṅkhāvitaraṇī', 'sn' => 0],
        ['book' => 200, 'paragraph' => 1, 'title' => 'vinayavinicchayo', 'sn' => 0],
    ]);
    $guid = wikiTerm($channel, 'refs', ['note' => "甲。{{para|id=207-1331|title=a 1331|style=reference}}乙。{{para|id=200-9955|title=b 9955|style=reference}}\n\n## 参考文献\n\n{{ref}}"]);

    $html = $this->get("/library/wiki/zh-Hans/{$guid}")->assertOk()->getContent();
    expect(substr_count($html, 'class="tpl-reference-item"'))->toBe(2)
        ->and($html)->toContain('</span><br /><span class="tpl-reference-item">');
});

it('follows a redirect to the target term in the same channel', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $target = wikiTerm($channel, 'nibbāna', ['tag' => '涅槃']);
    wikiTerm($channel, 'nirvāṇa', ['redirect' => 'nibbāna@涅槃']);

    $this->get('/library/wiki/zh-Hans/nirv%C4%81%E1%B9%87a')->assertRedirect(route('library.wiki.show', ['zh-Hans', $target]));
});

it('hides terms of private channels and unknown words', function () {
    $owner = makeStudio('owner');
    $private = makeChannel($owner);
    Channel::where('uid', $private)->update(['status' => Channel::STATUS_PRIVATE]);
    $guid = wikiTerm($private, 'secret');

    $this->get("/library/wiki/zh-Hans/{$guid}")->assertNotFound();
    $this->get('/library/wiki/zh-Hans/secret')->assertNotFound();
    $this->get('/library/wiki/zh-Hans/no-such-word')->assertNotFound();
});
