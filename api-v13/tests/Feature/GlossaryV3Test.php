<?php

use App\Models\DhammaTerm;
use App\Services\V3\GlossaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * GET /v3/glossaries/{grammar|community}：系统 channel 里的术语，整表、按词排序。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function glossaryTerm(string $channel, string $word, array $attrs = []): DhammaTerm
{
    $term = new DhammaTerm;
    $term->forceFill($attrs + [
        'id' => random_int(1, PHP_INT_MAX),
        'guid' => (string) Str::uuid(),
        'word' => $word,
        'word_en' => $word,
        'meaning' => "{$word}-义",
        'channal' => $channel,
        'language' => 'zh-Hans',
        'owner' => config('mint.admin.root_uuid'),
        'editor_id' => 0,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();

    return $term;
}

it('lists the community glossary of a language, sorted by word', function () {
    $root = config('mint.admin.root_uuid');
    $zh = makeChannel($root, '_community_term_zh-hans_');
    $grammar = makeChannel($root, '_System_Grammar_Term_zh-hans_');
    glossaryTerm($zh, 'sīla', ['tag' => '', 'other_meaning' => '戒律']);
    glossaryTerm($zh, 'dhamma');
    glossaryTerm($grammar, 'vibhatti');
    glossaryTerm($zh, 'deleted')->delete();

    $this->getJson('/api/v3/glossaries/community?lang=zh-Hans')
        ->assertOk()
        ->assertJsonPath('meta', ['kind' => 'community', 'lang' => 'zh-Hans', 'total' => 2])
        ->assertJsonPath('data.*.word', ['dhamma', 'sīla'])
        ->assertJsonPath('data.1.tag', null)
        ->assertJsonPath('data.1.other_meaning', '戒律');

    $this->getJson('/api/v3/glossaries/grammar?lang=zh-Hans')->assertJsonPath('data.*.word', ['vibhatti']);
});

it('falls back to English, and defaults to the negotiated interface language', function () {
    $root = config('mint.admin.root_uuid');
    $en = makeChannel($root, '_community_term_en_');
    $zh = makeChannel($root, '_community_term_zh-hans_');
    glossaryTerm($en, 'en-word');
    glossaryTerm($zh, 'zh-word');

    $this->getJson('/api/v3/glossaries/community?lang=my')
        ->assertJsonPath('meta.lang', 'en')
        ->assertJsonPath('data.*.word', ['en-word']);
    $this->getJson('/api/v3/glossaries/community', ['Accept-Language' => 'zh-CN'])
        ->assertJsonPath('meta.lang', 'zh-Hans')
        ->assertJsonPath('data.*.word', ['zh-word']);
});

it('returns an empty list when neither the language nor English has the glossary', function () {
    $this->getJson('/api/v3/glossaries/grammar?lang=zh-Hans')
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
});

it('rejects an unknown kind or a malformed language', function () {
    $this->getJson('/api/v3/glossaries/other')->assertNotFound();
    $this->getJson('/api/v3/glossaries/community?lang='.urlencode('../x'))
        ->assertUnprocessable()->assertJsonValidationErrors('lang');
});

it('gives the term guid only in the grammar glossary, and never the auto-increment id', function () {
    $root = config('mint.admin.root_uuid');
    $grammar = glossaryTerm(makeChannel($root, '_System_Grammar_Term_zh-hans_'), 'vibhatti');
    $community = glossaryTerm(makeChannel($root, '_community_term_zh-hans_'), 'dhamma');

    $row = $this->getJson('/api/v3/glossaries/grammar?lang=zh-Hans')->json('data.0');
    expect(array_keys($row))->toBe(['id', 'word', 'tag', 'meaning', 'other_meaning'])
        ->and($row['id'])->toBe($grammar->guid)
        ->and(json_encode($row))->not->toContain((string) $grammar->id);

    $row = $this->getJson('/api/v3/glossaries/community?lang=zh-Hans')->json('data.0');
    expect(array_keys($row))->toBe(['word', 'tag', 'meaning', 'other_meaning'])
        ->and(json_encode($row))->not->toContain($community->guid)
        ->and(json_encode($row))->not->toContain((string) $community->id);
});

it('answers 304 for an unchanged community glossary without loading it, and 200 after the task rotates the ETag', function () {
    $zh = makeChannel(config('mint.admin.root_uuid'), '_community_term_zh-hans_');
    glossaryTerm($zh, 'dhamma');

    $first = $this->getJson('/api/v3/glossaries/community?lang=zh-Hans')->assertOk();
    $etag = $first->headers->get('ETag');
    expect($etag)->not->toBeEmpty()
        ->and($first->headers->get('Cache-Control'))->toContain('no-cache')->toContain('public')
        ->and($first->headers->get('Vary'))->toContain('Accept-Language');

    DB::enableQueryLog();
    $this->getJson('/api/v3/glossaries/community?lang=zh-Hans', ['If-None-Match' => $etag])
        ->assertStatus(304)
        ->assertHeader('ETag', $etag)
        ->assertContent('');
    // nginx gzip 会把 ETag 改成弱校验 W/"…"，浏览器带回来的也是它
    $this->getJson('/api/v3/glossaries/community?lang=zh-Hans', ['If-None-Match' => 'W/'.$etag])->assertStatus(304);
    $loaded = collect(DB::getQueryLog())->contains(fn ($q) => str_contains($q['query'], 'dhamma_terms'));
    expect($loaded)->toBeFalse();

    app(GlossaryService::class)->rotateEtag($zh);

    $fresh = $this->getJson('/api/v3/glossaries/community?lang=zh-Hans', ['If-None-Match' => $etag])->assertOk();
    expect($fresh->headers->get('ETag'))->not->toBe($etag)
        ->and($fresh->json('data.*.word'))->toBe(['dhamma']);
});

it('gives no ETag for the grammar glossary or a missing table', function () {
    $grammar = makeChannel(config('mint.admin.root_uuid'), '_System_Grammar_Term_zh-hans_');
    glossaryTerm($grammar, 'vibhatti');

    expect($this->getJson('/api/v3/glossaries/grammar?lang=zh-Hans')->assertOk()->headers->has('ETag'))->toBeFalse()
        ->and($this->getJson('/api/v3/glossaries/community?lang=zh-Hans')->assertOk()->headers->has('ETag'))->toBeFalse();
});
