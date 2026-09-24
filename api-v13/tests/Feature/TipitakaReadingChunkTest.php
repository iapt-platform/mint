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
 * book 9002 一个 5 段的章节（起始段落号 1），每段 100 字节。$translated 列出
 * 哪些段落有译文，默认整章都有；传子集即模拟残缺译文。
 *
 * @param  array<int, int>  $translated
 */
function makeChapterFixture(array $translated = [1, 2, 3, 4, 5]): string
{
    $channel = makeChannel(makeStudio('chapter-owner'), 'chapter channel');
    makePaliText(9002, 1, 100, 5);
    foreach (range(2, 5) as $para) {
        makePaliText(9002, $para, 100);
    }
    foreach ($translated as $para) {
        makeSentence($channel, 9002, $para, 1, "para {$para} text");
    }

    return $channel;
}

it('fetches a block from the start and follows next_cursor', function () {
    $channel = makeChapterFixture();
    $base = "/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=2";

    // 不带 after，从章节开头取起
    $first = $this->getJson($base)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 2])
        ->and($first['meta'])->toMatchArray([
            'page_size' => 2,
            'page_size_unit' => 'para',
            'total' => 5,
            'remaining' => 3,
            'next_cursor' => '9002-2',
        ]);

    // 断点续传：把 next_cursor 原样传回 after
    $second = $this->getJson($base.'&after='.$first['meta']['next_cursor'])->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([3, 4])
        ->and($second['meta'])->toMatchArray(['remaining' => 1, 'next_cursor' => '9002-4']);

    // 最后一块：next_cursor 为 null 即取完
    $last = $this->getJson($base.'&after='.$second['meta']['next_cursor'])->assertOk()->json();
    expect(array_column($last['data'], 'para'))->toBe([5])
        ->and($last['meta']['remaining'])->toBe(0)
        ->and($last['meta']['next_cursor'])->toBeNull();
});

it('returns an empty block when the cursor is past the end', function () {
    $channel = makeChapterFixture();

    // keyset 语义：游标在范围之后就是空结果，不是错误。游标由服务端给出，
    // 客户端不自己拼，所以不必再校验它落在哪个区间
    $json = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&after=9002-5")
        ->assertOk()
        ->json();

    expect($json['data'])->toBe([])
        ->and($json['meta']['next_cursor'])->toBeNull()
        ->and($json['meta']['remaining'])->toBe(0);
});

it('scopes the chapter by chapter_len', function () {
    $channel = makeChapterFixture();

    // para=3 那一行没有 chapter_len，区间只有它自己
    expect($this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=3")
        ->assertOk()->json('meta'))
        ->toMatchArray(['total' => 1, 'remaining' => 0, 'next_cursor' => null]);
});

it('skips paragraphs that have no translation', function () {
    // 只译了 1、3、5；游标停在第 1 段之后，下一块直接跳到 3 和 5
    $channel = makeChapterFixture([1, 3, 5]);

    $block = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=2&after=9002-1")
        ->assertOk()
        ->json();

    expect(array_column($block['data'], 'para'))->toBe([3, 5])
        ->and($block['meta'])->toMatchArray([
            'total' => 3,
            'remaining' => 0,
            'next_cursor' => null,
        ]);
});

it('counts only the paragraphs this channel has actually translated', function () {
    // 5 段的章节里只译了 1、3、5，分块按这 3 段算，不会切出空块
    $channel = makeChapterFixture([1, 3, 5]);
    $base = "/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=2";

    $first = $this->getJson($base)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 3])
        ->and($first['meta'])->toMatchArray(['total' => 3, 'remaining' => 1, 'next_cursor' => '9002-3']);

    $second = $this->getJson($base.'&after=9002-3')->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([5])
        ->and($second['meta']['remaining'])->toBe(0);
});

it('fetches a block by byte size using the lenght column', function () {
    $channel = makeChapterFixture();
    // 每段 100 字节，250 字节累加到第 3 段才超过上限
    $base = "/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=250&unit=byte";

    $first = $this->getJson($base)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 2, 3])
        ->and($first['meta'])->toMatchArray([
            'page_size' => 250,
            'page_size_unit' => 'byte',
            'remaining' => 2,
        ]);

    $second = $this->getJson($base.'&after='.$first['meta']['next_cursor'])->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([4, 5])
        ->and($second['meta']['remaining'])->toBe(0);
});

it('counts bytes from pali_texts for the translated paragraphs only', function () {
    // 只译了 1、3、5；每段原文 100 字节，250b 累加到第三段（para 5）才超限
    $channel = makeChapterFixture([1, 3, 5]);

    $first = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=250&unit=byte")
        ->assertOk()
        ->json();

    expect(array_column($first['data'], 'para'))->toBe([1, 3, 5])
        ->and($first['meta']['remaining'])->toBe(0);
});

it('returns at least one paragraph when it alone exceeds the byte size', function () {
    $channel = makeChapterFixture();

    $items = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=1&unit=byte")
        ->assertOk()
        ->json('data');

    expect(array_column($items, 'para'))->toBe([1]);
});

it('returns an empty collection when the channel has nothing translated here', function () {
    $channel = makeChapterFixture([]);

    // 集合端点，没内容就是空集合；章节本身存在，不是 404
    $json = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1")
        ->assertOk()
        ->json();

    expect($json['data'])->toBe([])
        ->and($json['meta'])->toMatchArray(['total' => 0, 'remaining' => 0, 'next_cursor' => null]);
});

it('clamps page_size to the per-block ceiling and reports the effective value', function () {
    $channel = makeChapterFixture();
    $base = "/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1";

    // 段落模式上限 200
    expect($this->getJson($base.'&page_size=1000')->assertOk()->json('meta'))
        ->toMatchArray(['page_size' => 200, 'page_size_unit' => 'para']);

    // 字节模式上限 5000
    expect($this->getJson($base.'&page_size=20000&unit=byte')->assertOk()->json('meta'))
        ->toMatchArray(['page_size' => 5000, 'page_size_unit' => 'byte']);

    // 没到上限的照常返回原值
    expect($this->getJson($base.'&page_size=3')->assertOk()->json('meta.page_size'))->toBe(3);
});

it('accepts the cursor together with the include parameter', function () {
    $channel = makeChapterFixture();

    $block = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=1&after=9002-1&include=all")
        ->assertOk()
        ->json();

    expect($block['data'])->toHaveCount(1)
        ->and($block['data'][0])->toHaveKeys(['book', 'para', 'display', 'sentences'])
        ->and($block['data'][0]['para'])->toBe(2);
});

it('rejects bad filters and an unknown chapter', function () {
    $channel = makeChapterFixture();

    // 路径上只有 channel，格式不对匹配不上路由 → 404
    $this->getJson('/api/v3/tipitaka-reading/not-a-uuid')->assertStatus(404);

    // chapter 在 pali_texts 里不存在 → 404
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=99")
        ->assertStatus(404)
        ->assertJsonPath('title', 'Resource not found.');

    // 其余都是 filter，走 FormRequest → 422
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=0")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['page_size']]);
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&page_size=10p")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['page_size']]);
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&unit=bytes")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['unit']]);
    // 游标格式不对
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9002&chapter=1&after=not-a-cursor")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['after']]);
});
