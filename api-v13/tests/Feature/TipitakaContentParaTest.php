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

beforeEach(function () {
    $this->channel = makeChannel(makeStudio('para-owner'), 'para channel');
    makeSentence($this->channel, 9001, 1, 1, 'first sentence');
    makeSentence($this->channel, 9001, 1, 2, 'second sentence');
    makeSentence($this->channel, 9001, 2, 1, 'other paragraph');
});

it('renders every sentence of a paragraph wrapped in divs', function () {
    $data = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$this->channel}")
        ->assertOk()
        ->json('data');

    expect($data['para'])->toBe(1);
    expect($data['sentences'])->toHaveCount(2);
    expect($data['display'])
        ->toContain("<div class='translation' data-para='1'>")
        ->toContain("<div class='sentence' data-sid='9001-1-1-1'>")
        ->toContain("<div class='sentence' data-sid='9001-1-2-2'>")
        ->toContain("<div class='para-block'>");
});

it('wraps a chapter title paragraph in a heading', function () {
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

    $display = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$this->channel}")
        ->assertOk()
        ->json('data.display');

    expect($display)->toContain('<h2>')->not->toContain('para-block');
});

it('lists the paragraphs of a range and skips empty ones', function () {
    $items = $this->getJson("/api/v2/tipitaka-content-para?book=9001&para=1&to=3&channel={$this->channel}")
        ->assertOk()
        ->json('data.items');

    expect(array_column($items, 'para'))->toBe([1, 2]);
});

it('outputs one line per sentence for non html formats', function () {
    $display = $this->getJson("/api/v2/tipitaka-content-para/9001-1?channel={$this->channel}&format=text")
        ->assertOk()
        ->json('data.display');

    expect($display)->toBe("first sentence\nsecond sentence");
});

it('rejects an invalid id or channel', function () {
    $this->getJson("/api/v2/tipitaka-content-para/bad-id?channel={$this->channel}")
        ->assertJsonPath('ok', false);
    $this->getJson('/api/v2/tipitaka-content-para/9001-1?channel=not-a-uuid')
        ->assertJsonPath('ok', false);
    $this->getJson("/api/v2/tipitaka-content-para/9001-9?channel={$this->channel}")
        ->assertJsonPath('ok', false);
});

it('caches the paragraph and drops the cache when a sentence changes', function () {
    $url = "/api/v2/tipitaka-content-para/9001-1?channel={$this->channel}";
    $this->getJson($url)->assertOk();

    $tag = PaliContentService::paragraphCacheTag(9001, 1, $this->channel);
    $key = PaliContentService::paragraphCacheKey(9001, 1, $this->channel, 'html');
    expect(Cache::tags([$tag])->has($key))->toBeTrue();

    $sentence = Sentence::where('book_id', 9001)->where('paragraph', 1)->orderBy('word_start')->first();
    $sentence->content = 'changed sentence';
    $sentence->save();

    expect(Cache::tags([$tag])->has($key))->toBeFalse();
    expect($this->getJson($url)->json('data.display'))->toContain('changed sentence');
});

it('drops the cache when a sentence is added or deleted', function () {
    $url = "/api/v2/tipitaka-content-para/9001-1?channel={$this->channel}";
    $this->getJson($url)->assertOk();

    makeSentence($this->channel, 9001, 1, 3, 'third sentence');
    expect($this->getJson($url)->json('data.sentences'))->toHaveCount(3);

    Sentence::where('book_id', 9001)->where('paragraph', 1)->where('word_start', 3)->first()->delete();
    expect($this->getJson($url)->json('data.sentences'))->toHaveCount(2);
});
