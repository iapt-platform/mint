<?php

use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\PaliContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 建一个句子，返回模型
 */
function makeSentence(string $channelUid, int $book, int $para, int $wordStart, string $content): Sentence
{
    $sentence = new Sentence;
    $sentence->forceFill([
        // sentences.id 不是自增列，必须显式给值
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'book_id' => $book,
        'paragraph' => $para,
        'word_start' => $wordStart,
        'word_end' => $wordStart,
        'channel_uid' => $channelUid,
        'editor_uid' => (string) Str::uuid(),
        'content' => $content,
        'content_type' => 'markdown',
        'strlen' => mb_strlen($content),
        'status' => 30,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
        'language' => 'zh-Hans',
    ])->save();

    return $sentence;
}

/**
 * 建测试用的 channel 和句子：book 9001 的第 1 段两句，第 2 段一句。返回 channel uid
 */
function makeParagraphFixture(): string
{
    $channel = makeChannel(makeStudio('para-owner'), 'para channel');
    makeSentence($channel, 9001, 1, 1, 'first sentence');
    makeSentence($channel, 9001, 1, 2, 'second sentence');
    makeSentence($channel, 9001, 2, 1, 'other paragraph');

    return $channel;
}

it('renders every sentence of a paragraph wrapped in divs', function () {
    $channel = makeParagraphFixture();
    $data = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$channel}")
        ->assertOk()
        ->json('data');

    expect($data['para'])->toBe(1);
    // 默认只输出 display
    expect($data)->not->toHaveKey('sentences');
    expect($data['display'])
        ->toContain("<div class='translation' data-para='1'>")
        ->toContain("<div class='sentence' data-sid='9001-1-1-1'>")
        ->toContain("<div class='sentence' data-sid='9001-1-2-2'>")
        ->toContain("<div class='para-block'>");
});

it('wraps a chapter title paragraph in a heading', function () {
    $channel = makeParagraphFixture();
    (new PaliText)->forceFill([
        'book' => 9001,
        'paragraph' => 1,
        'level' => 2,
        'class' => '',
        'toc' => '',
        'text' => '',
        'html' => '',
        'pcd_book_id' => 0,
        'uid' => (string) Str::uuid(),
    ])->save();

    $display = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$channel}")
        ->assertOk()
        ->json('data.display');

    expect($display)->toContain('<h2>')->not->toContain('para-block');
});

it('can output only the sentences or both', function () {
    $channel = makeParagraphFixture();
    $sentences = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$channel}&view=sentences")
        ->assertOk()
        ->json('data');
    expect($sentences)->not->toHaveKey('display');
    expect($sentences['sentences'])->toHaveCount(2);
    expect($sentences['sentences'][0])->toHaveKeys(['sid', 'html']);

    $all = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$channel}&view=all")
        ->assertOk()
        ->json('data');
    expect($all)->toHaveKeys(['para', 'display', 'sentences']);

    $items = $this->getJson("/api/v2/tipitaka-content-para?book=9001&para=1&channel={$channel}&view=sentences")
        ->assertOk()
        ->json('data.items');
    expect($items[0])->not->toHaveKey('display');
    expect($items[0]['sentences'])->toHaveCount(2);
});

it('lists the paragraphs of a range and skips empty ones', function () {
    $channel = makeParagraphFixture();
    $items = $this->getJson("/api/v2/tipitaka-content-para?book=9001&para=1&to=3&channel={$channel}")
        ->assertOk()
        ->json('data.items');

    expect(array_column($items, 'para'))->toBe([1, 2]);
});

it('outputs one line per sentence for non html formats', function () {
    $channel = makeParagraphFixture();
    $display = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$channel}&format=text")
        ->assertOk()
        ->json('data.display');

    expect($display)->toBe("first sentence\nsecond sentence");
});

it('rejects an invalid id or channel', function () {
    $channel = makeParagraphFixture();
    $this->getJson("/api/v2/tipitaka-content-para/bad-id?channel={$channel}")
        ->assertJsonPath('ok', false);
    $this->getJson('/api/v2/tipitaka-content-para/9001-1?channel=not-a-uuid')
        ->assertJsonPath('ok', false);
    $this->getJson("/api/v2/tipitaka-content-para/9001-9?channel={$channel}")
        ->assertJsonPath('ok', false);
});

it('caches the paragraph and drops the cache when a sentence changes', function () {
    $channel = makeParagraphFixture();
    $url = "/api/v2/tipitaka-content-para/9001-1?channel={$channel}";
    $this->getJson($url)->assertOk();

    $tag = PaliContentService::paragraphCacheTag(9001, 1, $channel);
    $key = PaliContentService::paragraphCacheKey(9001, 1, $channel, 'html');
    expect(Cache::tags([$tag])->has($key))->toBeTrue();

    $sentence = Sentence::where('book_id', 9001)->where('paragraph', 1)->orderBy('word_start')->first();
    $sentence->content = 'changed sentence';
    $sentence->save();

    expect(Cache::tags([$tag])->has($key))->toBeFalse();
    expect($this->getJson($url)->json('data.display'))->toContain('changed sentence');
});

it('drops the cache when a sentence is added or deleted', function () {
    $channel = makeParagraphFixture();
    $url = "/api/v2/tipitaka-content-para/9001-1?channel={$channel}";
    $this->getJson($url)->assertOk();

    makeSentence($channel, 9001, 1, 3, 'third sentence');
    expect($this->getJson($url.'&view=sentences')->json('data.sentences'))->toHaveCount(3);

    Sentence::where('book_id', 9001)->where('paragraph', 1)->where('word_start', 3)->first()->delete();
    expect($this->getJson($url.'&view=sentences')->json('data.sentences'))->toHaveCount(2);
});
