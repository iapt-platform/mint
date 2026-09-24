<?php

use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\PaliContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

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
    $data = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=1")
        ->assertOk()
        ->json('data.0');

    expect($data['para'])->toBe(1);
    // 默认只输出 display
    expect($data)->not->toHaveKey('sentences');
    expect($data['display'])
        ->toContain("<div id='para-1' class='translation' data-para='1'>")
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

    $display = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=1")
        ->assertOk()
        ->json('data.0.display');

    expect($display)->toContain('<h2>')->not->toContain('para-block');
});

it('can output only the sentences or both', function () {
    $channel = makeParagraphFixture();
    $sentences = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=1&include=sentences")
        ->assertOk()
        ->json('data.0');
    expect($sentences)->not->toHaveKey('display');
    expect($sentences['sentences'])->toHaveCount(2);
    expect($sentences['sentences'][0])->toHaveKeys(['sid', 'html']);

    $all = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=1&include=all")
        ->assertOk()
        ->json('data.0');
    expect($all)->toHaveKeys(['para', 'display', 'sentences']);
});

it('lists the paragraphs of a range and skips empty ones', function () {
    $channel = makeParagraphFixture();
    $items = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=1&to=3")
        ->assertOk()
        ->json('data');

    expect(array_column($items, 'para'))->toBe([1, 2]);
});

it('outputs one line per sentence for non html formats', function () {
    $channel = makeParagraphFixture();
    $display = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=1&format=text")
        ->assertOk()
        ->json('data.0.display');

    expect($display)->toBe("first sentence\nsecond sentence");
});

it('rejects a malformed channel in the path but validates filters as 422', function () {
    $channel = makeParagraphFixture();

    // 路径参数由路由约束把关：不是 uuid 就匹配不上任何路由 → 404
    $this->getJson('/api/v3/tipitaka-reading/not-a-uuid')->assertStatus(404);

    // filter 是查询参数，走 FormRequest → 422
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=not-a-number")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['para']]);
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=not-a-number")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['book']]);

    // book 只在跟 chapter / para / to 一起用时必填
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?para=1")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['book']]);

    // chapter 与 para 互斥
    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&chapter=1&para=1")
        ->assertStatus(422);
});

it('fetches the whole channel when no filter is given', function () {
    $channel = makeParagraphFixture();

    // 不带任何查询串 = 取这个 channel 的全部译文，下载场景要的就是这个
    $json = $this->getJson("/api/v3/tipitaka-reading/{$channel}")->assertOk()->json();

    expect(array_column($json['data'], 'para'))->toBe([1, 2])
        ->and($json['data'][0]['book'])->toBe(9001)
        // 无 book 过滤时不给 total / remaining，靠 next_cursor 判断结束
        ->and($json['meta'])->not->toHaveKey('total')
        ->and($json['meta']['next_cursor'])->toBeNull();
});

it('returns an empty collection for a paragraph with no content', function () {
    $channel = makeParagraphFixture();

    // 这里是集合端点，没内容就是空集合而不是 404——区间口径下空段落本来就被跳过，
    // 单段只是区间长度为 1 的特例，两者口径要一致
    $json = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=9")
        ->assertOk()
        ->json();

    expect($json['data'])->toBe([])
        ->and($json['meta'])->toMatchArray(['total' => 0, 'remaining' => 0])
        ->and($json['meta']['next_cursor'])->toBeNull();
});

it('rejects a range that ends before it starts', function () {
    $channel = makeParagraphFixture();

    $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9001&para=3&to=1")
        ->assertStatus(422)
        ->assertJsonPath('errors.to.0', __('site.invalid_parameter'));
});

it('caches the paragraph and drops the cache when a sentence changes', function () {
    $channel = makeParagraphFixture();
    $url = "/api/v3/tipitaka-reading/{$channel}?book=9001&para=1";
    $this->getJson($url)->assertOk();

    $key = PaliContentService::paragraphCacheKey(9001, 1, $channel, 'html');
    expect(Cache::has($key))->toBeTrue();

    $sentence = Sentence::where('book_id', 9001)->where('paragraph', 1)->orderBy('word_start')->first();
    $sentence->content = 'changed sentence';
    $sentence->save();

    expect(Cache::has($key))->toBeFalse();
    expect($this->getJson($url)->json('data.0.display'))->toContain('changed sentence');
});

it('drops the cache when a sentence is added or deleted', function () {
    $channel = makeParagraphFixture();
    $url = "/api/v3/tipitaka-reading/{$channel}?book=9001&para=1";
    $this->getJson($url)->assertOk();

    makeSentence($channel, 9001, 1, 3, 'third sentence');
    expect($this->getJson($url.'&include=sentences')->json('data.0.sentences'))->toHaveCount(3);

    Sentence::where('book_id', 9001)->where('paragraph', 1)->where('word_start', 3)->first()->delete();
    expect($this->getJson($url.'&include=sentences')->json('data.0.sentences'))->toHaveCount(2);
});
