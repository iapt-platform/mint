<?php

use App\Models\Discussion;
use App\Services\PaliContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 阅读页批注内嵌：type='commentary'（注释书对应，带出处）与 type='note'（普通边注，无出处）。
 */

/** 建 channel + 一句根本译文（book 9101 第 1 段），返回 [channel uid, 句子] */
function makeAnnotatedSentence(): array
{
    $channel = makeChannel(makeStudio('anno-owner'), 'anno channel');
    $sentence = makeSentence($channel, 9101, 1, 1, 'first sentence');

    return [$channel, $sentence];
}

function makeAnnotation(string $resId, string $type, string $content, int $posEnd): Discussion
{
    $discussion = new Discussion;
    $discussion->forceFill([
        'res_id' => $resId,
        'res_type' => 'sentence',
        'type' => $type,
        'title' => $content,
        'content' => $content,
        'content_type' => 'markdown',
        'editor_uid' => makeStudio('anno-'.Str::random(6)),
        'pos_start' => 0,
        'pos_end' => $posEnd,
        'quote_exact' => 'first',
    ])->save();

    return $discussion;
}

it('injects a commentary annotation with a cite link', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    // 义注句：commentary 的 content 是它的句子模板，渲染成它在同 channel 的译文
    makeSentence($channel, 9102, 7, 3, '义注这么说');
    makeAnnotation($sentence->uid, 'commentary', '{{9102-7-3-3}}', 5);

    $display = $this->getJson("/api/v3/tipitaka-read-para/9101-1?channel={$channel}")
        ->assertOk()
        ->json('data.display');

    expect($display)
        ->toContain('义注这么说')
        ->toContain('<cite class="anno-jump" data-book="9102" data-para="7" data-start="3" data-end="3">义注</cite>');
});

it('injects a plain note annotation without a cite link', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    // 普通边注：content 就是注解正文本身
    makeAnnotation($sentence->uid, 'note', '这里的 dassana 指见到佛陀。', 5);

    $display = $this->getJson("/api/v3/tipitaka-read-para/9101-1?channel={$channel}")
        ->assertOk()
        ->json('data.display');

    // 正文照原样进 sidenote，紧跟 </span> 收尾——没有 <cite> 出处
    expect($display)
        ->toContain('这里的 dassana 指见到佛陀。</span>')
        ->not->toContain('anno-jump');
});

it('injects both kinds on the same sentence, note first at the same position', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    makeSentence($channel, 9102, 7, 3, '义注这么说');
    makeAnnotation($sentence->uid, 'commentary', '{{9102-7-3-3}}', 5);
    makeAnnotation($sentence->uid, 'note', '译者按。', 5);

    $display = $this->getJson("/api/v3/tipitaka-read-para/9101-1?channel={$channel}")
        ->assertOk()
        ->json('data.display');

    expect(mb_strpos($display, '译者按。'))->toBeLessThan(mb_strpos($display, '义注这么说'));
});

it('drops the paragraph cache when either kind of annotation changes', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    $url = "/api/v3/tipitaka-read-para/9101-1?channel={$channel}";
    $this->getJson($url)->assertOk();

    $key = PaliContentService::paragraphCacheKey(9101, 1, $channel, 'html');
    expect(Cache::has($key))->toBeTrue();

    makeAnnotation($sentence->uid, 'note', '译者按。', 5);
    expect(Cache::has($key))->toBeFalse();
    expect($this->getJson($url)->json('data.display'))->toContain('译者按。');
});
