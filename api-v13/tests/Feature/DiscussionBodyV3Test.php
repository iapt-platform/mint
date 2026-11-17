<?php

use App\Models\DhammaTerm;
use App\Models\Discussion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * discussions 的正文渲染：`body` 是 App\Services\Markup 的节点树，`summary` 是纯文本。
 * 只有挂在句子上的才渲染（模板要用句子所在 channel 解析）。
 */
function makeBodyTopic(string $owner, string $channel, string $content, array $attrs = []): Discussion
{
    $sentence = makeSentence($channel, 1, 1, random_int(1, 100000), 'sentence text');

    return Discussion::forceCreate(array_merge([
        'res_type' => 'sentence',
        'res_id' => $sentence->uid,
        'type' => 'discussion',
        'title' => 'topic',
        'content' => $content,
        'editor_uid' => $owner,
    ], $attrs));
}

function makeBodyTerm(string $channel, string $owner, string $word, string $meaning): void
{
    (new DhammaTerm)->forceFill([
        'id' => random_int(1, PHP_INT_MAX), 'guid' => (string) Str::uuid(), 'word' => $word, 'word_en' => $word,
        'meaning' => $meaning, 'channal' => $channel, 'owner' => $owner, 'language' => 'zh-Hans',
        'editor_id' => 0, 'create_time' => time() * 1000, 'modify_time' => time() * 1000,
    ])->save();
}

it('renders the body as a node tree and the summary as text', function () {
    $owner = makeStudio('body-owner');
    $channel = makeChannel($owner);
    makeBodyTerm($channel, $owner, 'dhamma', '法');
    $topic = makeBodyTopic($owner, $channel, '关于[[dhamma]]{{n|脚注}}的讨论');

    $data = $this->getJson("/api/v3/discussions/{$topic->id}", authHeader($owner))->assertOk()->json('data');

    expect($data)->not->toHaveKey('html');
    expect($data['body'][0])->toBe(['type' => 'text', 'value' => '关于']);
    expect($data['body'][1])->toMatchArray(['type' => 'tpl', 'name' => 'term']);
    expect($data['body'][1]['props'])->toMatchArray(['word' => 'dhamma', 'meaning' => '法', 'parentChannelId' => $channel]);
    expect($data['body'][2])->toMatchArray(['type' => 'tpl', 'name' => 'note', 'children' => [['type' => 'text', 'value' => '脚注']]]);
    expect($data['summary'])->toBe('关于法的讨论');
});

it('does not render discussions that are not on a sentence', function () {
    $owner = makeStudio('body-owner2');
    $topic = Discussion::forceCreate([
        'res_type' => 'article', 'res_id' => (string) Str::uuid(), 'type' => 'discussion',
        'title' => 't', 'content' => '[[dhamma]]', 'editor_uid' => $owner,
    ]);

    $data = $this->getJson("/api/v3/discussions/{$topic->id}", authHeader($owner))->assertOk()->json('data');
    expect($data)->not->toHaveKey('body')->not->toHaveKey('summary');
});

it('writes v3 content as markdown-e2 and renders backticks as code', function () {
    $owner = makeStudio('body-owner-e2');
    $channel = makeChannel($owner);
    $sentence = makeSentence($channel, 1, 1, 7, 'sentence text');

    $data = $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence->uid, 'type' => 'discussion',
        'title' => 't', 'content' => '用 `{{n|x}}` 写脚注', 'notify' => false,
    ], authHeader($owner))->assertCreated()->json('data');

    expect($data['content_type'])->toBe('markdown-e2');
    expect($data['body'][1])->toMatchArray(['type' => 'el', 'tag' => 'code', 'children' => [['type' => 'text', 'value' => '{{n|x}}']]]);
    // v2 / v4 照旧读 content_type
    $row = Discussion::find($data['id']);
    expect($row->content_type)->toBe('markdown')->and($row->content_type_v2)->toBe('markdown-e2');

    $reply = $this->postJson("/api/v3/discussions/{$data['id']}/replies", ['content' => '`code`', 'notify' => false], authHeader($owner))
        ->assertCreated()->json('data');
    expect($reply['content_type'])->toBe('markdown-e2');
    expect($reply['body'][0]['tag'])->toBe('code');
});

it('keeps v2 rows in the legacy dialect until their content is edited through v3', function () {
    $owner = makeStudio('body-owner-legacy');
    $channel = makeChannel($owner);
    $topic = makeBodyTopic($owner, $channel, '旧`脚注`写法');

    $data = $this->getJson("/api/v3/discussions/{$topic->id}", authHeader($owner))->assertOk()->json('data');
    expect($data['content_type'])->toBe('markdown');
    expect($data['body'][1])->toMatchArray(['type' => 'tpl', 'name' => 'note']);

    // 只改状态不动正文：方言不变
    $this->patchJson("/api/v3/discussions/{$topic->id}", ['status' => 'close'], authHeader($owner))->assertOk()
        ->assertJsonPath('data.content_type', 'markdown');

    // 经 v3 改了正文：按 E2 存
    $patched = $this->patchJson("/api/v3/discussions/{$topic->id}", ['content' => '新`代码`'], authHeader($owner))
        ->assertOk()->json('data');
    expect($patched['content_type'])->toBe('markdown-e2');
    expect($patched['body'][1]['tag'])->toBe('code');
    expect($topic->fresh()->content_type)->toBe('markdown');
});

it('looks up the terms of a whole page of discussions together', function () {
    $owner = makeStudio('body-owner4');
    $channel = makeChannel($owner);
    $sentence = makeSentence($channel, 1, 1, 1, 'sentence text');
    foreach (range(1, 10) as $i) {
        makeBodyTerm($channel, $owner, "w{$i}", "义{$i}");
        Discussion::forceCreate([
            'res_type' => 'sentence', 'res_id' => $sentence->uid, 'type' => 'discussion',
            'title' => "t{$i}", 'content' => "[[w{$i}]]", 'editor_uid' => $owner,
        ]);
    }

    DB::enableQueryLog();
    $data = $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$sentence->uid}", authHeader($owner))
        ->assertOk()->json('data');
    $termQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'from "dhamma_terms"'))->count();
    DB::disableQueryLog();

    expect(collect($data)->pluck('summary')->sort()->values()->all())->toContain('义1', '义10');
    // 本 channel 的术语、studio 术语各一次（没有社区术语 channel 时不查社区）
    expect($termQueries)->toBeLessThanOrEqual(3);
});
