<?php

use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Services\V3\GlossaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * upgrade:community.term：社区术语表（_community_term_{lang}_）只由这个任务维护。
 * 历史上 tag 的 NULL / '' / 空白各建了一行，导致同一个词重复；跑一次之后应恢复成每个 word + tag 一行。
 */

function communityTerm(string $channel, string $word, ?string $tag, array $attrs = []): DhammaTerm
{
    $term = new DhammaTerm;
    $term->forceFill($attrs + [
        'id' => random_int(1, PHP_INT_MAX),
        'guid' => (string) Str::uuid(),
        'word' => $word,
        'word_en' => $word,
        'tag' => $tag,
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

beforeEach(function () {
    $root = config('mint.admin.root_uuid');
    makeChannel($root, '_System_Pali_VRI_');
    $this->community = makeChannel($root, '_community_term_zh-hans_');
    $this->user = makeChannel($root, 'user channel');
});

function communityRows(string $channel): array
{
    return DhammaTerm::where('channal', $channel)->orderBy('word')->orderBy('tag')->get()
        ->map(fn ($t) => [$t->word, $t->tag, $t->meaning])->all();
}

it('merges NULL, empty and blank tags into one row per word', function () {
    communityTerm($this->user, 'dhamma', null, ['meaning' => '法']);
    communityTerm($this->user, 'dhamma', '', ['meaning' => '法']);
    communityTerm($this->user, 'dhamma', '  ', ['meaning' => '法']);
    communityTerm($this->user, 'dhamma', 'ārammaṇa', ['meaning' => '法']);

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertSuccessful();

    expect(communityRows($this->community))->toBe([
        ['dhamma', 'ārammaṇa', '法'],
        ['dhamma', null, '法'],
    ]);
});

it('heals existing duplicates, keeping the oldest row, and is stable on rerun', function () {
    communityTerm($this->user, 'sīla', '', ['meaning' => '戒']);
    $oldest = communityTerm($this->community, 'sīla', null, ['create_time' => 1000]);
    $dupA = communityTerm($this->community, 'sīla', '', ['create_time' => 2000]);
    $dupB = communityTerm($this->community, 'sīla', ' ', ['create_time' => 3000]);

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertSuccessful();
    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertSuccessful();

    expect(communityRows($this->community))->toBe([['sīla', null, '戒']])
        ->and(DhammaTerm::where('channal', $this->community)->value('guid'))->toBe($oldest->guid)
        ->and(DhammaTerm::onlyTrashed()->whereIn('guid', [$dupA->guid, $dupB->guid])->count())->toBe(2);
});

it('does not count its own rows as sources: rows without user terms are removed', function () {
    communityTerm($this->user, 'dhamma', null, ['meaning' => '法']);
    // 社区里自己的一票不该影响译名，也不该让没有来源的行继续存在
    communityTerm($this->community, 'dhamma', null, ['meaning' => '旧译名']);
    communityTerm($this->community, 'orphan', null);

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertSuccessful();

    expect(communityRows($this->community))->toBe([['dhamma', null, '法']]);
});

it('never takes the note from an AI-generated channel, but still counts its meaning', function () {
    $ai = makeChannel(config('mint.admin.root_uuid'), 'ai channel');
    Channel::where('uid', $ai)->update(['source_type' => 'ai']);
    communityTerm($ai, 'dhamma', null, ['meaning' => '法', 'note' => 'AI 写的注释']);
    communityTerm($ai, 'dhamma', null, ['meaning' => '法']);
    communityTerm($this->user, 'dhamma', null, ['meaning' => '达摩']);

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertSuccessful();

    $term = DhammaTerm::where('channal', $this->community)->sole();
    expect($term->meaning)->toBe('法')
        ->and((string) $term->note)->toBe('');
});

it('rotates the community glossary ETag after each run', function () {
    communityTerm($this->user, 'dhamma', null);
    $glossaries = app(GlossaryService::class);
    $before = $glossaries->etag('community', 'zh-Hans');

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertSuccessful();

    expect($glossaries->etag('community', 'zh-Hans'))->not->toBe($before);
});

it('only touches the given word when one is passed', function () {
    communityTerm($this->user, 'dhamma', null);
    communityTerm($this->user, 'sīla', null);
    communityTerm($this->community, 'sīla', '');
    communityTerm($this->community, 'sīla', null);

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans', 'word' => 'dhamma'])->assertSuccessful();

    expect(communityRows($this->community))->toHaveCount(3);
});

it('refuses to wipe the channel when no source terms are found', function () {
    communityTerm($this->community, 'dhamma', null);

    $this->artisan('upgrade:community.term', ['lang' => 'zh-Hans'])->assertFailed();

    expect(communityRows($this->community))->toHaveCount(1);
});
