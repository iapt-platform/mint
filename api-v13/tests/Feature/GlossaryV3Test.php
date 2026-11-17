<?php

use App\Models\DhammaTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('never exposes the auto-increment id of the terms', function () {
    $zh = makeChannel(config('mint.admin.root_uuid'), '_community_term_zh-hans_');
    $term = glossaryTerm($zh, 'dhamma');

    $row = $this->getJson('/api/v3/glossaries/community?lang=zh-Hans')->json('data.0');
    expect(array_keys($row))->toBe(['id', 'word', 'tag', 'meaning', 'other_meaning'])
        ->and($row['id'])->toBe($term->guid)
        ->and(json_encode($row))->not->toContain((string) $term->id);
});
