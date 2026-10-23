<?php

use App\Facades\RedisCache;
use App\Models\Discussion;
use App\Services\PaliContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $display = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9101&para=1")
        ->assertOk()
        ->json('data.0.display');

    expect($display)
        ->toContain('义注这么说')
        ->toContain('<cite class="anno-jump" data-book="9102" data-para="7" data-start="3" data-end="3">义注</cite>');
});

it('injects a plain note annotation without a cite link', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    // 普通边注：content 就是注解正文本身
    makeAnnotation($sentence->uid, 'note', '这里的 dassana 指见到佛陀。', 5);

    $display = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9101&para=1")
        ->assertOk()
        ->json('data.0.display');

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

    $display = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9101&para=1")
        ->assertOk()
        ->json('data.0.display');

    expect(mb_strpos($display, '译者按。'))->toBeLessThan(mb_strpos($display, '义注这么说'));
});

it('drops the paragraph cache when either kind of annotation changes', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    $url = "/api/v3/tipitaka-reading/{$channel}?book=9101&para=1";
    $this->getJson($url)->assertOk();

    $key = PaliContentService::paragraphCacheKey(9101, 1, $channel, 'html');
    expect(RedisCache::has($key))->toBeTrue();

    makeAnnotation($sentence->uid, 'note', '译者按。', 5);
    expect(RedisCache::has($key))->toBeFalse();
    expect($this->getJson($url)->json('data.0.display'))->toContain('译者按。');
});

/* ------------------------------------------------------------------ glossary 与 issue */

function makeAnchored(string $resId, string $type, array $attrs): Discussion
{
    $discussion = new Discussion;
    $discussion->forceFill(array_merge([
        'res_id' => $resId,
        'res_type' => 'sentence',
        'type' => $type,
        'title' => 't',
        'content' => 'body',
        'content_type' => 'markdown',
        'editor_uid' => makeStudio('anno-'.Str::random(6)),
    ], $attrs))->save();

    return $discussion;
}

function readFirstParagraph(string $channel): string
{
    return test()->getJson("/api/v3/tipitaka-reading/{$channel}?book=9101&para=1")->assertOk()->json('data.0.display');
}

it('replaces a glossary anchor with a term reference', function () {
    [$channel, $sentence] = makeAnnotatedSentence();   // 'first sentence'
    makeAnchored($sentence->uid, 'glossary', ['title' => 'paṭhama', 'pos_start' => 0, 'pos_end' => 5, 'quote_exact' => 'first']);

    expect(readFirstParagraph($channel))
        ->toContain("data-term='paṭhama'")
        ->toContain('>first</span> sentence');
});

it('highlights issues, merging overlaps, and marks issues without a position at the sentence end', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    $a = makeAnchored($sentence->uid, 'issue', ['severity' => 'suggestion', 'pos_start' => 0, 'pos_end' => 8, 'quote_exact' => 'first se']);
    $b = makeAnchored($sentence->uid, 'issue', ['severity' => 'error', 'pos_start' => 6, 'pos_end' => 14, 'quote_exact' => 'sentence']);
    $c = makeAnchored($sentence->uid, 'issue', ['severity' => 'warning']);

    expect(readFirstParagraph($channel))
        ->toContain('<span class="anno-issue anno-issue-error" data-discussion-ids="'.$a->id.','.$b->id.'" data-severity="error">first sentence</span>')
        ->toContain('<span class="anno-issue-mark anno-issue-warning" data-discussion-ids="'.$c->id.'"');
});

it('relocates an issue by its quote when the position is stale', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    makeAnchored($sentence->uid, 'issue', ['severity' => 'error', 'pos_start' => 0, 'pos_end' => 3, 'quote_exact' => 'sentence']);

    expect(readFirstParagraph($channel))->toContain('data-severity="error">sentence</span>');
});

it('does not inject closed issues or glossaries, and drops the cache when one is closed', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    makeAnchored($sentence->uid, 'glossary', ['title' => 'x', 'pos_start' => 0, 'pos_end' => 5, 'quote_exact' => 'first', 'status' => 'close']);
    $issue = makeAnchored($sentence->uid, 'issue', ['severity' => 'error', 'pos_start' => 6, 'pos_end' => 14]);

    expect(readFirstParagraph($channel))->toContain('anno-issue')->not->toContain('term-ref');

    $issue->forceFill(['status' => 'close'])->save();   // 关闭会清缓存
    expect(readFirstParagraph($channel))->not->toContain('anno-issue');
});

it('leaves issue markup out of plain text output but keeps the glossary term', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    makeAnchored($sentence->uid, 'issue', ['severity' => 'error']);
    makeAnchored($sentence->uid, 'glossary', ['title' => 'paṭhama', 'pos_start' => 0, 'pos_end' => 5]);

    $text = app(PaliContentService::class)->readParagraph(9101, 1, $channel, 'text')['display'];

    expect($text)->toBe('first sentence');
});

/* ------------------------------------------------------------------ 评估 */

it('marks an assessed sentence with a check coloured by score, after any issue mark', function (float $score, string $level) {
    [$channel, $sentence] = makeAnnotatedSentence();
    makeAnchored($sentence->uid, 'issue', ['severity' => 'warning']);
    $assessment = makeAnchored($sentence->uid, 'assessment', ['title' => null, 'severity' => 'mqm', 'score' => $score]);

    $display = readFirstParagraph($channel);

    expect($display)
        ->toContain('<span class="anno-assessment-mark anno-assessment-'.$level.'" data-discussion-ids="'.$assessment->id.'"')
        ->toContain('data-level="'.$level.'"')
        ->toContain('<i class="ti ti-circle-check"></i>')
        ->and(mb_strpos($display, 'anno-issue-mark'))->toBeLessThan(mb_strpos($display, 'anno-assessment-mark'));
})->with([
    'high' => [80.0, 'high'],
    'medium' => [79.99, 'medium'],
    'low' => [12.5, 'low'],
]);

it('drops the cache when an assessment is rescored, and leaves it out of plain text', function () {
    [$channel, $sentence] = makeAnnotatedSentence();
    $assessment = makeAnchored($sentence->uid, 'assessment', ['title' => null, 'severity' => 'mqm', 'score' => 30]);

    expect(readFirstParagraph($channel))->toContain('data-score="30"');

    $assessment->forceFill(['score' => 95.5])->save();
    expect(readFirstParagraph($channel))->toContain('data-score="95.5"')->toContain('anno-assessment-high');

    $text = app(PaliContentService::class)->readParagraph(9101, 1, $channel, 'text')['display'];
    expect($text)->toBe('first sentence');
});

it('marks unassessed sentences grey only in a paragraph that has an assessed sentence', function () {
    [$channel, $sentence] = makeAnnotatedSentence();   // 9101-1，第一句
    makeSentence($channel, 9101, 1, 2, 'second sentence');
    makeSentence($channel, 9101, 2, 1, 'other paragraph');
    makeAnchored($sentence->uid, 'assessment', ['title' => null, 'severity' => 'mqm', 'score' => 90]);

    $display = readFirstParagraph($channel);
    expect(substr_count($display, 'anno-assessment-high'))->toBe(1)
        ->and(substr_count($display, 'anno-assessment-none'))->toBe(1)
        ->and($display)->toContain('second sentence<span class="anno-assessment-mark anno-assessment-none"');

    $other = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9101&para=2")->assertOk()->json('data.0.display');
    expect($other)->not->toContain('anno-assessment');

    $text = app(PaliContentService::class)->readParagraph(9101, 1, $channel, 'text')['display'];
    expect($text)->not->toContain('anno-assessment');
});
