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

    $first = $this->getJson($url)->assertOk()->json('data');
    expect(array_column($first['items'], 'para'))->toBe([1, 2]);
    expect($first['pagination'])->toMatchArray([
        'page' => 1,
        'pageSize' => '2p',
        'total' => 5,
        'from' => 1,
        'to' => 2,
        'hasMore' => true,
    ]);

    $last = $this->getJson($url.'&page=3')->assertOk()->json('data');
    expect(array_column($last['items'], 'para'))->toBe([5]);
    expect($last['pagination']['hasMore'])->toBeFalse();

    $this->getJson($url.'&page=4')->assertJsonPath('ok', false);
});

it('pages a chapter by byte size using the lenght column', function () {
    $channel = makeChapterFixture();
    // 每段 100 字节，250b 累加到第 3 段才超过上限
    $url = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=250b";

    $first = $this->getJson($url)->assertOk()->json('data');
    expect(array_column($first['items'], 'para'))->toBe([1, 2, 3]);

    $second = $this->getJson($url.'&page=2')->assertOk()->json('data');
    expect(array_column($second['items'], 'para'))->toBe([4, 5]);
    expect($second['pagination']['hasMore'])->toBeFalse();
});

it('returns at least one paragraph when it alone exceeds the byte size', function () {
    $channel = makeChapterFixture();
    $items = $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=1b")
        ->assertOk()
        ->json('data.items');

    expect(array_column($items, 'para'))->toBe([1]);
});

it('accepts the id form and the view parameter', function () {
    $channel = makeChapterFixture();
    $items = $this->getJson("/api/v3/tipitaka-read-chapter/9002-1?channel={$channel}&pagesize=1p&view=all")
        ->assertOk()
        ->json('data.items');

    expect($items)->toHaveCount(1);
    expect($items[0])->toHaveKeys(['para', 'display', 'sentences']);
});

it('rejects a bad id, channel, pagesize or unknown chapter', function () {
    $channel = makeChapterFixture();
    $this->getJson("/api/v3/tipitaka-read-chapter/bad-id?channel={$channel}")
        ->assertJsonPath('ok', false);
    $this->getJson('/api/v3/tipitaka-read-chapter/9002-1?channel=not-a-uuid')
        ->assertJsonPath('ok', false);
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=99&channel={$channel}")
        ->assertJsonPath('ok', false);
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=20000")
        ->assertStatus(422);
});
