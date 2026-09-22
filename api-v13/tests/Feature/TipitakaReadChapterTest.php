<?php

use App\Models\PaliText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 建一个 pali_texts 段落。第一段带 chapter_len 表示章节长度
 */
function makePaliText(int $book, int $para, int $lenght, ?int $chapterLen = null): void
{
    (new PaliText)->forceFill([
        'book' => $book,
        'paragraph' => $para,
        'level' => $chapterLen ? 2 : 9,
        'class' => '',
        'toc' => '',
        'text' => '',
        'html' => '',
        'lenght' => $lenght,
        'chapter_len' => $chapterLen,
        'pcd_book_id' => 0,
        'uid' => (string) Str::uuid(),
    ])->save();
}

/**
 * book 9002 一个 5 段的章节，每段 100 字节，每段一句
 */
function makeChapterFixture(): string
{
    $channel = makeChannel(makeStudio('chapter-owner'), 'chapter channel');
    makePaliText(9002, 1, 100, 5);
    foreach (range(2, 5) as $para) {
        makePaliText(9002, $para, 100);
    }
    foreach (range(1, 5) as $para) {
        makeSentence($channel, 9002, $para, 1, "para {$para} text");
    }

    return $channel;
}

it('pages a chapter by paragraph count', function () {
    $channel = makeChapterFixture();
    $url = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=2p";

    $first = $this->getJson($url)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 2]);
    expect($first['meta'])->toMatchArray([
        'current_page' => 1,
        'page_size' => '2p',
        'total' => 5,
        'first_para' => 1,
        'last_para' => 2,
        'has_more' => true,
    ]);

    $last = $this->getJson($url.'&page=3')->assertOk()->json();
    expect(array_column($last['data'], 'para'))->toBe([5]);
    expect($last['meta']['has_more'])->toBeFalse();

    $this->getJson($url.'&page=4')
        ->assertStatus(422)
        ->assertJsonPath('errors.page.0', __('site.invalid_parameter'));
});

it('pages a chapter by byte size using the lenght column', function () {
    $channel = makeChapterFixture();
    // 每段 100 字节，250b 累加到第 3 段才超过上限
    $url = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=250b";

    $first = $this->getJson($url)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 2, 3]);

    $second = $this->getJson($url.'&page=2')->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([4, 5]);
    expect($second['meta']['has_more'])->toBeFalse();
});

it('returns at least one paragraph when it alone exceeds the byte size', function () {
    $channel = makeChapterFixture();
    $items = $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=1b")
        ->assertOk()
        ->json('data');

    expect(array_column($items, 'para'))->toBe([1]);
});

it('accepts the id form and the view parameter', function () {
    $channel = makeChapterFixture();
    $items = $this->getJson("/api/v3/tipitaka-read-chapter/9002-1?channel={$channel}&pagesize=1p&view=all")
        ->assertOk()
        ->json('data');

    expect($items)->toHaveCount(1);
    expect($items[0])->toHaveKeys(['para', 'display', 'sentences']);
});

it('rejects a bad id, channel, pagesize or unknown chapter', function () {
    $channel = makeChapterFixture();
    // v3 用真实状态码 + RFC 9457 Problem Details，不再看 ok 字段
    $this->getJson("/api/v3/tipitaka-read-chapter/bad-id?channel={$channel}")
        ->assertStatus(422)
        ->assertJsonStructure(['type', 'title', 'status', 'detail']);
    $this->getJson('/api/v3/tipitaka-read-chapter/9002-1?channel=not-a-uuid')
        ->assertStatus(422)
        ->assertJsonPath('errors.channel.0', __('site.invalid_parameter'));
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=99&channel={$channel}")
        ->assertStatus(404)
        ->assertJsonPath('title', 'Resource not found.');
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=20000")
        ->assertStatus(422);
});
