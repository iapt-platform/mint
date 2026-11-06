<?php

use App\Models\BookTitle;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * library 三藏栏目的新路由：按译本浏览、作品详情、阅读入口与旧地址 301。
 *
 * 分类用真实的 public/data/category/default.json：id 3 = 长部 › 戒蕴品，
 * tag 为 sutta + dīghanikāya + sīlakkhandhavagga。
 */
function libPaliText(int $book, int $paragraph, int $level, int $chapterLen, string $toc = ''): PaliText
{
    $row = new PaliText;
    $row->forceFill([
        'book' => $book,
        'paragraph' => $paragraph,
        'level' => $level,
        'class' => '',
        'toc' => $toc,
        'text' => '',
        'html' => '',
        'pcd_book_id' => 0,
        'chapter_len' => $chapterLen,
        'chapter_strlen' => 100,
        'uid' => (string) Str::uuid(),
    ])->save();

    return $row;
}

function libTagPaliText(PaliText $text, array $names): void
{
    foreach ($names as $name) {
        $tag = Tag::where('name', $name)->first();
        if (! $tag) {
            $tag = new Tag;
            $tag->forceFill(['id' => (string) Str::uuid(), 'name' => $name, 'color' => 0, 'owner_id' => (string) Str::uuid()])->save();
        }
        (new TagMap)->forceFill([
            'id' => (string) Str::uuid(), 'table_name' => 'pali_texts', 'anchor_id' => $text->uid, 'tag_id' => $tag->id,
        ])->save();
    }
}

function libProgress(string $channel, int $book, int $para, float $progress, ?string $title = null): ProgressChapter
{
    $row = new ProgressChapter;
    $row->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'book' => $book,
        'para' => $para,
        'lang' => 'zh-Hans',
        'channel_id' => $channel,
        'progress' => $progress,
        'title' => $title,
        'all_trans' => 0,
        'public' => 0,
    ])->save();

    return $row;
}

/**
 * 一部作品（sn 9100 = book 9100 的 3–102 段，两个章节 4、50），归入分类 3；
 * channel 收录了作品根节点（40%）和第二个章节。
 *
 * @return array{channel: string, sn: int}
 */
function libTipitakaFixture(): array
{
    $root = libPaliText(9100, 3, 1, 100, 'Sīlakkhandhavaggapāḷi');
    libPaliText(9100, 4, 2, 46, '1. Brahmajālasuttaṃ');
    libPaliText(9100, 50, 2, 53, '2. Sāmaññaphalasuttaṃ');
    libTagPaliText($root, ['sutta', 'dīghanikāya', 'sīlakkhandhavagga', 'pāḷi']);
    // book_titles.title 存的是丛书名，作品名取起点段落的 toc
    (new BookTitle)->forceFill(['sn' => 9100, 'book' => 9100, 'paragraph' => 3, 'title' => 'dīghanikāyapāḷi'])->save();

    $channel = makeChannel(makeStudio('lib-owner'), 'lib channel');
    libProgress($channel, 9100, 3, 0.4, '戒蕴品');
    libProgress($channel, 9100, 50, 0.8, '沙门果经');

    return ['channel' => $channel, 'sn' => 9100];
}

it('lists public channels with their work counts', function () {
    ['channel' => $channel] = libTipitakaFixture();
    $private = makeChannel(makeStudio('lib-private'), 'secret channel');
    Channel::where('uid', $private)->update(['status' => 10]);
    libProgress($private, 9100, 3, 0.9);

    $this->get('/library/tipitaka/channels')
        ->assertOk()
        ->assertSee('lib channel')
        ->assertSee("/library/tipitaka/channels/{$channel}", false)
        ->assertDontSee('secret channel');

    // 类型过滤：fixture 的 channel 是 translation
    $this->get('/library/tipitaka/channels?type=nissaya')->assertOk()->assertDontSee('lib channel');
    $this->get('/library/tipitaka/channels?type=translation')->assertOk()->assertSee('lib channel');

    // 语言过滤用 channel_lang；?lang= 是界面语言，不过滤列表
    $this->get('/library/tipitaka/channels?channel_lang=en')->assertOk()->assertDontSee('lib channel');
    $this->get('/library/tipitaka/channels?lang=en')->assertOk()->assertSee('lib channel');
});

it('lists the works of a channel with progress', function () {
    ['channel' => $channel] = libTipitakaFixture();

    $this->get("/library/tipitaka/channels/{$channel}")
        ->assertOk()
        ->assertSee('戒蕴品')
        ->assertSee("/library/tipitaka/channels/{$channel}/work/9100", false)
        ->assertSee('40%');
});

it('returns 404 for an unknown channel', function () {
    $this->get('/library/tipitaka/channels/'.Str::uuid())->assertNotFound();
});

it('shows the work under its category in the channel tree', function () {
    ['channel' => $channel] = libTipitakaFixture();
    $other = makeChannel(makeStudio('lib-other'), 'other channel');

    $this->get("/library/tipitaka/channels/{$channel}/category/1")
        ->assertOk()
        ->assertSee('戒蕴品')
        ->assertSee("/library/tipitaka/channels/{$channel}/work/9100", false);

    // 没收录的 channel 默认不列，?all=1 时置灰列出
    $this->get("/library/tipitaka/channels/{$other}/category/1")
        ->assertOk()
        ->assertDontSee('Sīlakkhandhavaggapāḷi');
    $this->get("/library/tipitaka/channels/{$other}/category/1?all=1")
        ->assertOk()
        ->assertSee('Sīlakkhandhavaggapāḷi')
        ->assertSee('is-missing');
});

it('shows every top category when no category is given', function () {
    ['channel' => $channel] = libTipitakaFixture();

    // 「全部」：四个藏都作为顶层，本 channel 只在经藏下有作品，所以只画出经藏这一棵
    $this->get("/library/tipitaka/channels/{$channel}/category")
        ->assertOk()
        ->assertSee('戒蕴品')
        ->assertSee("/library/tipitaka/channels/{$channel}/category/1", false);
});

it('returns 404 for an unknown category', function () {
    ['channel' => $channel] = libTipitakaFixture();

    $this->get("/library/tipitaka/channels/{$channel}/category/99999")->assertNotFound();
    $this->get('/library/tipitaka/category/99999')->assertNotFound();
});

it('shows the work detail with its chapters', function () {
    ['channel' => $channel, 'sn' => $sn] = libTipitakaFixture();

    $this->get("/library/tipitaka/channels/{$channel}/work/{$sn}")
        ->assertOk()
        ->assertSee('戒蕴品')
        ->assertSee("/library/tipitaka/channels/{$channel}/work/{$sn}/read", false)
        // 有译文的章节可点，没有的不出链接
        ->assertSee("/library/tipitaka/channels/{$channel}/chapter/9100-50", false)
        ->assertDontSee("/library/tipitaka/channels/{$channel}/chapter/9100-4\"", false);
});

it('returns 404 when the channel does not include the work', function () {
    ['sn' => $sn] = libTipitakaFixture();
    $other = makeChannel(makeStudio('lib-other'), 'other channel');

    $this->get("/library/tipitaka/channels/{$other}/work/{$sn}")->assertNotFound();
    $this->get("/library/tipitaka/channels/{$other}/work/424242")->assertNotFound();
});

it('redirects the read entry to the first translated chapter', function () {
    ['channel' => $channel, 'sn' => $sn] = libTipitakaFixture();

    $this->get("/library/tipitaka/channels/{$channel}/work/{$sn}/read")
        ->assertStatus(302)
        ->assertRedirect("/library/tipitaka/channels/{$channel}/chapter/9100-50");
});

it('falls back to the start of the work when no chapter is translated', function () {
    ['sn' => $sn] = libTipitakaFixture();
    $other = makeChannel(makeStudio('lib-other'), 'other channel');

    $this->get("/library/tipitaka/channels/{$other}/work/{$sn}/read")
        ->assertRedirect("/library/tipitaka/channels/{$other}/chapter/9100-3");
});

it('redirects the legacy reader url permanently and keeps the other query', function () {
    ['channel' => $channel] = libTipitakaFixture();

    $this->get("/library/tipitaka/9100-50/read?channel={$channel}&comm=abc")
        ->assertStatus(301)
        ->assertRedirect("/library/tipitaka/channels/{$channel}/chapter/9100-50?comm=abc");
});

it('redirects the legacy work url permanently', function () {
    ['channel' => $channel] = libTipitakaFixture();
    $uid = ProgressChapter::where('channel_id', $channel)->where('para', 3)->value('uid');

    $this->get("/library/tipitaka/{$uid}")
        ->assertStatus(301)
        ->assertRedirect("/library/tipitaka/channels/{$channel}/work/9100");
    $this->get('/library/tipitaka/'.Str::uuid())->assertNotFound();
});

it('lists the versions of a category and links them to the channel work page', function () {
    ['channel' => $channel] = libTipitakaFixture();

    $this->get('/library/tipitaka/category/3')
        ->assertOk()
        ->assertSee('lib channel')
        ->assertSee("/library/tipitaka/channels/{$channel}/work/9100", false);
});

it('links the works of a leaf category to the work versions page', function () {
    libTipitakaFixture();

    $this->get('/library/tipitaka/category/3')
        ->assertOk()
        ->assertSee('/library/tipitaka/work/9100', false)
        ->assertSee('Sīlakkhandhavaggapāḷi')
        ->assertDontSee('dīghanikāyapāḷi');
    // 旧的 ?book= 过滤 301 到作品版本页
    $this->get('/library/tipitaka/category/3?book=9100-3')
        ->assertStatus(301)
        ->assertRedirect('/library/tipitaka/work/9100');
});

it('lists every public version of a work grouped by type', function () {
    ['channel' => $channel] = libTipitakaFixture();
    $english = makeChannel(makeStudio('lib-en'), 'english channel');
    Channel::where('uid', $english)->update(['lang' => 'en']);
    libProgress($english, 9100, 3, 0.25);

    $this->get('/library/tipitaka/work/9100')
        ->assertOk()
        ->assertSee('lib channel')
        ->assertSee('english channel')
        ->assertSee("/library/tipitaka/channels/{$channel}/work/9100", false)
        ->assertSee('40%')
        ->assertSee('id="type-translation"', false);

    $this->get('/library/tipitaka/work/9100?channel_lang=en')
        ->assertOk()
        ->assertSee('english channel')
        ->assertDontSee('lib channel');

    $this->get('/library/tipitaka/work/424242')->assertNotFound();
});

it('redirects the legacy channel query to the channel pages', function () {
    ['channel' => $channel] = libTipitakaFixture();

    $this->get("/library/tipitaka?channel={$channel}")
        ->assertStatus(301)
        ->assertRedirect("/library/tipitaka/channels/{$channel}");
    $this->get("/library/tipitaka/category/3?channel={$channel}")
        ->assertStatus(301)
        ->assertRedirect("/library/tipitaka/channels/{$channel}/category/3");
});

it('renders the tipitaka home page', function () {
    libTipitakaFixture();

    $this->get('/library/tipitaka')
        ->assertOk()
        ->assertSee('lib channel')
        ->assertSee('/library/tipitaka/category/1', false);
});
