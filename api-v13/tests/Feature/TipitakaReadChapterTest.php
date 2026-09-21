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

it('fetches a block of paragraphs from the cursor, the default unit', function () {
    $channel = makeChapterFixture();
    $base = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=2";

    // 不传 from，游标默认落在章节开头
    $first = $this->getJson($base)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 2]);
    expect($first['meta'])->toMatchArray([
        'current_para' => 1,
        'total_para' => 5,
        'page_size' => 2,
        'page_size_unit' => 'para',
        'book' => 9002,
        'chapter' => 1,
        'first_para' => 1,
        'last_para' => 2,
        'remaining_para' => 3,
    ]);

    // 断点续传：把上次的 last_para + 1 当作下次的 from
    $second = $this->getJson($base.'&from=3')->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([3, 4]);
    expect($second['meta'])->toMatchArray(['current_para' => 3, 'remaining_para' => 1]);

    $last = $this->getJson($base.'&from=5')->assertOk()->json();
    expect(array_column($last['data'], 'para'))->toBe([5]);
    expect($last['meta']['remaining_para'])->toBe(0);

    // 游标越过章节区间（本章节是 1..5）
    $this->getJson($base.'&from=6')
        ->assertStatus(422)
        ->assertJsonPath('errors.from.0', __('site.invalid_parameter'));
});

it('rejects a cursor outside the chapter range', function () {
    $channel = makeChapterFixture();

    // 章节区间是 1..5，超出两端都不行
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&from=99")
        ->assertStatus(422)
        ->assertJsonPath('errors.from.0', __('site.invalid_parameter'));

    // para=3 那一行没有 chapter_len，区间只有它自己；从第 1 段起就在区间之前
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=3&channel={$channel}&from=1")
        ->assertStatus(422)
        ->assertJsonPath('errors.from.0', __('site.invalid_parameter'));

    // 落在区间内就正常取
    expect($this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=3&channel={$channel}&from=3")
        ->assertOk()->json('meta'))
        ->toMatchArray(['chapter' => 3, 'total_para' => 1, 'first_para' => 3, 'remaining_para' => 0]);
});

it('rejects a cursor that is inside the chapter but past the last translation', function () {
    // 章节区间 1..5，只译了 1、2、3；游标 5 在区间内但之后没有译文了
    $channel = makeChapterFixture([1, 2, 3]);

    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&from=5")
        ->assertStatus(422)
        ->assertJsonPath('errors.from.0', __('site.invalid_parameter'));
});

it('advances the cursor to the next translated paragraph', function () {
    // 只译了 1、3、5；游标落在没有译文的第 2 段，顺延到第 3 段
    $channel = makeChapterFixture([1, 3, 5]);

    $block = $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=2&from=2")
        ->assertOk()
        ->json();

    expect(array_column($block['data'], 'para'))->toBe([3, 5]);
    expect($block['meta'])->toMatchArray([
        'current_para' => 2,   // 请求的游标原样回显
        'first_para' => 3,     // 实际取到的第一段
        'last_para' => 5,
        'total_para' => 3,
        'remaining_para' => 0,
    ]);
});

it('counts only the paragraphs this channel has actually translated', function () {
    // 5 段的章节里只译了 1、3、5，分块按这 3 段算，不会切出空块
    $channel = makeChapterFixture([1, 3, 5]);
    $base = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=2";

    $first = $this->getJson($base)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 3]);
    expect($first['meta'])->toMatchArray([
        'total_para' => 3,
        'first_para' => 1,
        'last_para' => 3,
        'remaining_para' => 1,
    ]);

    $second = $this->getJson($base.'&from=4')->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([5]);
    expect($second['meta']['remaining_para'])->toBe(0);
});

it('fetches a block by byte size using the lenght column', function () {
    $channel = makeChapterFixture();
    // 每段 100 字节，250 字节累加到第 3 段才超过上限
    $base = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=250&unit=byte";

    $first = $this->getJson($base)->assertOk()->json();
    expect(array_column($first['data'], 'para'))->toBe([1, 2, 3]);
    expect($first['meta'])->toMatchArray([
        'page_size' => 250,
        'page_size_unit' => 'byte',
        'remaining_para' => 2,
    ]);

    $second = $this->getJson($base.'&from=4')->assertOk()->json();
    expect(array_column($second['data'], 'para'))->toBe([4, 5]);
    expect($second['meta']['remaining_para'])->toBe(0);
});

it('counts bytes from pali_texts for the translated paragraphs only', function () {
    // 只译了 1、3、5；每段原文 100 字节，250b 累加到第三段（para 5）才超限
    $channel = makeChapterFixture([1, 3, 5]);

    $first = $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=250&unit=byte")
        ->assertOk()
        ->json();

    expect(array_column($first['data'], 'para'))->toBe([1, 3, 5]);
    expect($first['meta']['remaining_para'])->toBe(0);
});

it('returns at least one paragraph when it alone exceeds the byte size', function () {
    $channel = makeChapterFixture();

    $items = $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=1&unit=byte")
        ->assertOk()
        ->json('data');

    expect(array_column($items, 'para'))->toBe([1]);
});

it('returns 404 when the channel has nothing translated in this chapter', function () {
    $channel = makeChapterFixture([]);

    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}")
        ->assertStatus(404)
        ->assertJsonPath('title', 'Resource not found.');
});

it('clamps pagesize to the per-block ceiling and reports the effective value', function () {
    $channel = makeChapterFixture();
    $base = "/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}";

    // 段落模式上限 200
    expect($this->getJson($base.'&pagesize=1000')->assertOk()->json('meta'))
        ->toMatchArray(['page_size' => 200, 'page_size_unit' => 'para']);

    // 字节模式上限 5000
    expect($this->getJson($base.'&pagesize=20000&unit=byte')->assertOk()->json('meta'))
        ->toMatchArray(['page_size' => 5000, 'page_size_unit' => 'byte']);

    // 没到上限的照常返回原值
    expect($this->getJson($base.'&pagesize=3')->assertOk()->json('meta.page_size'))->toBe(3);
});

it('accepts the id form, the cursor and the view parameter', function () {
    $channel = makeChapterFixture();

    $block = $this->getJson("/api/v3/tipitaka-read-chapter/9002-1?channel={$channel}&pagesize=1&from=2&view=all")
        ->assertOk()
        ->json();

    expect($block['data'])->toHaveCount(1);
    expect($block['data'][0])->toHaveKeys(['para', 'display', 'sentences']);
    expect($block['meta'])->toMatchArray(['chapter' => 1, 'first_para' => 2, 'remaining_para' => 3]);
});

it('rejects a bad id, channel, pagesize, unit, cursor or unknown chapter', function () {
    $channel = makeChapterFixture();
    // v3 用真实状态码 + RFC 9457 Problem Details，不再看 ok 字段
    $this->getJson("/api/v3/tipitaka-read-chapter/bad-id?channel={$channel}")
        ->assertStatus(422)
        ->assertJsonStructure(['type', 'title', 'status', 'detail']);
    $this->getJson('/api/v3/tipitaka-read-chapter/9002-1?channel=not-a-uuid')
        ->assertStatus(422)
        ->assertJsonPath('errors.channel.0', __('site.invalid_parameter'));
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&channel={$channel}")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['para']]);
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=99&channel={$channel}")
        ->assertStatus(404)
        ->assertJsonPath('title', 'Resource not found.');
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=0")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['pagesize']]);
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&pagesize=10p")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['pagesize']]);
    $this->getJson("/api/v3/tipitaka-read-chapter?book=9002&para=1&channel={$channel}&unit=bytes")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['unit']]);
    $this->getJson("/api/v3/tipitaka-read-chapter/9002-1?channel={$channel}&from=0")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['from']]);
});
