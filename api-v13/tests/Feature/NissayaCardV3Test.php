<?php

use App\Models\DhammaTerm;
use App\Models\NissayaEnding;
use App\Models\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * GET /v3/nissaya-cards/{ending}：缅文词尾卡片（取代 v2 nissaya-card）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function grammarTerm(string $channel, string $word, string $meaning, ?string $note = null): void
{
    (new DhammaTerm)->forceFill([
        'id' => random_int(1, PHP_INT_MAX), 'guid' => (string) Str::uuid(), 'word' => $word, 'word_en' => $word,
        'meaning' => $meaning, 'note' => $note, 'channal' => $channel, 'language' => 'zh-Hans',
        'owner' => config('mint.admin.root_uuid'), 'editor_id' => 0, 'content_type' => 'markdown-e2',
        'create_time' => time() * 1000, 'modify_time' => time() * 1000,
    ])->save();
}

function relation(string $name, ?array $from, ?array $to, ?string $category = null): void
{
    (new Relation)->forceFill([
        'id' => (string) Str::uuid(), 'name' => $name, 'from' => $from ? json_encode($from) : null,
        'to' => $to ? json_encode($to) : null, 'category' => $category, 'editor_id' => (string) Str::uuid(),
    ])->save();
}

function nissayaEnding(string $ending, string $relation, string $lang = 'my', ?array $from = null): void
{
    (new NissayaEnding)->forceFill([
        'id' => (string) Str::uuid(), 'ending' => $ending, 'relation' => $relation, 'lang' => $lang,
        'from' => $from ? json_encode($from) : null, 'strlen' => mb_strlen($ending), 'count' => 0,
        'editor_id' => (string) Str::uuid(),
    ])->save();
}

beforeEach(function () {
    $this->zh = makeChannel(config('mint.admin.root_uuid'), '_System_Grammar_Term_zh-hans_');
});

it('builds the card of an ending with its term and relations', function () {
    grammarTerm($this->zh, 'သည်', '主格语尾', '主格的**标记**。');
    grammarTerm($this->zh, 'SV', '主谓关系');
    grammarTerm($this->zh, 'nom', '主格');
    grammarTerm($this->zh, 'SV active', '主动分词', '说明**正文**。');
    relation('SV', ['case' => ['nom']], ['case' => ['nom', 'prp'], 'spell' => 'x'], 'SV active');
    relation('NOREL', null, null);
    nissayaEnding('သည်', 'SV');
    nissayaEnding('သည်', 'NOREL');
    nissayaEnding('是', 'SV', 'zh-Hans');                         // 翻译建议：from 为空
    nissayaEnding('乃', 'SV', 'zh-Hans', ['case' => ['nom']]);    // from 与关系相同
    nissayaEnding('非', 'SV', 'zh-Hans', ['case' => ['gen']]);    // from 不同：不算

    $data = $this->getJson('/api/v3/nissaya-cards/'.rawurlencode('သည်').'?lang=zh-Hans')->assertOk()->json('data');

    expect($data['ending'])->toBe('သည်')->and($data['lang'])->toBe('zh-Hans');
    expect($data['term'])->toMatchArray(['word' => 'သည်', 'meaning' => '主格语尾', 'tag' => null])
        ->and($data['term']['body'])->toBeArray()->not->toBeEmpty();
    expect(array_column($data['relations'], 'relation'))->toBe(['NOREL', 'SV']);
    expect($data['relations'][0])->toMatchArray(['from' => null, 'to' => null, 'category' => null, 'relation_label' => null]);
    expect($data['relations'][1])->toMatchArray([
        'relation_label' => '主谓关系',
        'from' => ['cases' => [['case' => 'nom', 'label' => '主格']], 'spell' => null],
        'to' => ['cases' => [['case' => 'nom', 'label' => '主格'], ['case' => 'prp', 'label' => null]], 'spell' => 'x'],
        'category' => ['name' => 'SV active', 'meaning' => '主动分词', 'note' => '说明正文。'],
        'local_endings' => ['是', '乃'],
    ]);
});

it('falls back to the English grammar terms, and returns an empty card for an unknown ending', function () {
    $en = makeChannel(config('mint.admin.root_uuid'), '_System_Grammar_Term_en_');
    grammarTerm($en, 'သည်', 'nominative ending');

    $this->getJson('/api/v3/nissaya-cards/'.rawurlencode('သည်').'?lang=th')
        ->assertOk()->assertJsonPath('data.lang', 'en')->assertJsonPath('data.term.meaning', 'nominative ending');
    $this->getJson('/api/v3/nissaya-cards/nothing?lang=zh-Hans')
        ->assertOk()->assertJsonPath('data.term', null)->assertJsonPath('data.relations', []);
    $this->getJson('/api/v3/nissaya-cards/x?lang='.urlencode('../x'))->assertUnprocessable()->assertJsonValidationErrors('lang');
});

it('builds a card with a constant number of queries', function () {
    $count = function (int $n) {
        $ending = "e{$n}";
        foreach (range(1, $n) as $i) {
            grammarTerm($this->zh, "case{$n}-{$i}", "格{$i}");
            relation("R{$n}-{$i}", ['case' => ["case{$n}-{$i}"]], ['case' => ["case{$n}-{$i}"]], "cat{$n}-{$i}");
            nissayaEnding($ending, "R{$n}-{$i}");
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/nissaya-cards/{$ending}?lang=zh-Hans")->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queries;
    };

    expect($count(6))->toBe($count(2));
});
