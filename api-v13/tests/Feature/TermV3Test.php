<?php

use App\Models\AiModel;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\TermCategory;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * v3 术语：/v3/terms、/v3/channels/{c}/terms、/v3/studios/{s}/terms。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

/** 在 channel 里经 API 建一条术语，返回响应 data */
function newTerm(string $channel, string $as, array $body = []): array
{
    return test()->postJson("/api/v3/channels/{$channel}/terms", $body + [
        'word' => 'dhamma', 'meaning' => '法', 'note' => "正文第一段。\n\n第二段。{{category|法义}} {{category|基本教义}}",
    ], authHeader($as))->assertCreated()->json('data');
}

function termGrant(string $human, AiModel $model, string $channel, int $book = 0): string
{
    return test()->postJson('/api/v3/agent-grants', [
        'ai_model' => $model->uid, 'target_type' => 'channel', 'target_id' => $channel, 'book' => $book,
    ], authHeader($human))->assertCreated()->json('data.token');
}

function modelAuth(AiModel $model, ?string $grant = null): array
{
    return array_filter([
        'Authorization' => 'Bearer '.AuthService::getUserToken($model->uid),
        'X-Agent-Grant' => $grant,
    ]);
}

/* ------------------------------------------------------------------ 建 */

it('creates a term in a channel, extracting categories and defaulting to markdown-e2', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);

    $data = newTerm($channel, $owner, ['tag' => '十七特征', 'quality' => 'draft', 'summary' => '一句话摘要。']);

    expect($data)
        ->word->toBe('dhamma')
        ->tag->toBe('十七特征')
        ->channel_id->toBe($channel)
        ->owner_id->toBe($owner)
        ->language->toBe('zh-Hans')
        ->content_type->toBe('markdown-e2')
        ->quality->toBe('draft')
        ->categories->toBe(['法义', '基本教义'])
        ->summary_text->toBe('一句话摘要。')
        ->co_editor->toBeNull()
        ->abilities->toBe(['update' => true, 'delete' => true]);
    expect($data['editor']['id'])->toBe($owner);
    expect($data['body'])->toBeArray()->not->toBeEmpty();
    expect(DhammaTerm::find($data['id']))->version->toBe(1)->editor_id->toBeGreaterThan(0);
});

it('never exposes the auto-increment id of term_categories', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $term = newTerm($channel, $owner);
    $ids = TermCategory::where('term_guid', $term['id'])->pluck('id')->all();
    expect($ids)->toHaveCount(2);

    $responses = [
        $this->getJson("/api/v3/terms/{$term['id']}")->json('data'),
        $this->getJson("/api/v3/channels/{$channel}/terms")->json('data.0'),
        $this->getJson('/api/v3/terms?word=dhamma&with_body=1')->json('data.0'),
    ];
    foreach ($responses as $data) {
        // 分类只以名字出现，没有对象、没有 id
        expect($data['categories'])->toBe(['法义', '基本教义']);
        foreach ($ids as $id) {
            expect(json_encode($data))->not->toMatch('/"id":\s*'.$id.'\b/');
        }
    }
});

it('rejects a duplicate word@tag in the same channel, treating an empty tag as none', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    newTerm($channel, $owner);

    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'dhamma', 'tag' => '', 'meaning' => '又一个'], authHeader($owner))
        ->assertStatus(409)
        ->assertJsonPath('type', fn ($type) => str_contains($type, 'term-exists'));
    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'dhamma', 'tag' => '另一义', 'meaning' => '又一个'], authHeader($owner))
        ->assertCreated();
});

it('validates term input', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);

    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x'], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('meaning');
    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x', 'meaning' => 'y', 'tag' => 'a@b'], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('tag');
    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x', 'meaning' => 'y', 'quality' => 'great'], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('quality');
    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x', 'meaning' => 'y', 'language' => 'en'], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('language');
    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x', 'meaning' => 'y', 'redirect' => 'x'], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('redirect');
    $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x', 'meaning' => 'y'])->assertUnauthorized();
});

it('lets team editors and above create terms in a team channel', function () {
    [$team, $users] = makeTeam();
    $channel = makeChannel($team);

    foreach (['owner', 'manager', 'editor'] as $role) {
        newTerm($channel, $users[$role], ['word' => "w-{$role}"]);
    }
    foreach (['reader', 'outsider'] as $role) {
        $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'x', 'meaning' => 'y'], authHeader($users[$role]))
            ->assertForbidden();
    }
    expect(DhammaTerm::where('channal', $channel)->value('owner'))->toBe($team);
});

it('creates studio-level terms for owners and managers, and lets editors update them', function () {
    [$team, $users] = makeTeam();
    $body = ['word' => 'sīla', 'meaning' => '戒', 'language' => 'zh-Hans'];

    $this->postJson("/api/v3/studios/{$team}/terms", $body, authHeader($users['editor']))->assertForbidden();
    $created = $this->postJson("/api/v3/studios/{$team}/terms", $body, authHeader($users['manager']))
        ->assertCreated()
        ->assertJsonPath('data.channel_id', null)
        ->json('data');

    $this->patchJson("/api/v3/terms/{$created['id']}", ['meaning' => '戒律'], authHeader($users['editor']) + ['If-Match' => $created['etag']])
        ->assertOk()->assertJsonPath('data.meaning', '戒律');
    $this->patchJson("/api/v3/terms/{$created['id']}", ['meaning' => 'x'], authHeader($users['reader']) + ['If-Match' => 'x'])
        ->assertForbidden();
});

/* ------------------------------------------------------------------ 读 */

it('shows a term with ETag, and hides terms of private channels', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $term = newTerm($channel, $owner);

    $res = $this->getJson("/api/v3/terms/{$term['id']}")->assertOk();
    expect($res->headers->get('ETag'))->toBe($term['etag']);
    expect($res->json('data.abilities'))->toBe(['update' => false, 'delete' => false]);
    expect($res->json('data.summary_text'))->toBe('正文第一段。');

    Channel::where('uid', $channel)->update(['status' => Channel::STATUS_PRIVATE]);
    $this->getJson("/api/v3/terms/{$term['id']}")->assertForbidden();
    $this->getJson("/api/v3/terms/{$term['id']}", authHeader($owner))->assertOk();
    $this->getJson('/api/v3/terms/'.Str::uuid())->assertNotFound();
});

it('renders the body against the requested channels, ignoring those the viewer cannot see', function () {
    $owner = makeStudio('owner');
    $home = makeChannel($owner, 'home');
    $other = makeChannel($owner, 'other');
    $private = makeChannel($owner, 'private');
    Channel::where('uid', $private)->update(['status' => Channel::STATUS_PRIVATE]);
    newTerm($home, $owner, ['word' => 'citta', 'meaning' => '本版心']);
    newTerm($other, $owner, ['word' => 'citta', 'meaning' => '他版心']);
    newTerm($private, $owner, ['word' => 'citta', 'meaning' => '私有心']);
    $term = newTerm($home, $owner, ['word' => 'host', 'note' => '见[[citta]]。']);
    $text = fn (array $res) => json_encode($res['data']['body'], JSON_UNESCAPED_UNICODE);

    expect($text($this->getJson("/api/v3/terms/{$term['id']}")->json()))->toContain('本版心');
    expect($text($this->getJson("/api/v3/terms/{$term['id']}?channels={$other}")->json()))->toContain('他版心');
    // 看不见的 channel 被忽略，退回术语所在 channel
    expect($text($this->getJson("/api/v3/terms/{$term['id']}?channels={$private}")->json()))->toContain('本版心');
    expect($text($this->getJson("/api/v3/terms/{$term['id']}?channels={$private}", authHeader($owner))->json()))->toContain('私有心');

    $this->getJson("/api/v3/terms/{$term['id']}?mode=edit")->assertOk();

    // format=html：正文换成新渲染器的 HTML 字符串
    $html = $this->getJson("/api/v3/terms/{$term['id']}?format=html")->assertOk()->json('data');
    expect($html)->not->toHaveKey('body');
    expect($html['html'])->toContain('class="term-ref"')->toContain('本版心');
    $this->getJson("/api/v3/terms/{$term['id']}?format=pdf")->assertUnprocessable()->assertJsonValidationErrors('format');
    $this->getJson("/api/v3/terms/{$term['id']}?mode=write")->assertUnprocessable()->assertJsonValidationErrors('mode');
    $this->getJson("/api/v3/terms/{$term['id']}?channels=nope")->assertUnprocessable()->assertJsonValidationErrors('channels');
});

it('lists the terms of a channel with filters', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    newTerm($channel, $owner, ['word' => 'a', 'quality' => 'featured']);
    newTerm($channel, $owner, ['word' => 'b', 'note' => '{{category|律学}}']);

    $this->getJson("/api/v3/channels/{$channel}/terms?order=word")
        ->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('data.*.word', ['a', 'b']);
    $this->getJson("/api/v3/channels/{$channel}/terms?quality=featured")->assertJsonPath('data.*.word', ['a']);
    $this->getJson("/api/v3/channels/{$channel}/terms?quality=pending")->assertJsonPath('data.*.word', ['b']);
    $this->getJson("/api/v3/channels/{$channel}/terms?category=".urlencode('律学'))->assertJsonPath('data.*.word', ['b']);
    $this->getJson("/api/v3/channels/{$channel}/terms?search=A")->assertJsonPath('data.*.word', ['a']);
    $this->getJson("/api/v3/channels/{$channel}/terms?quality=great")->assertUnprocessable();

    Channel::where('uid', $channel)->update(['status' => Channel::STATUS_PRIVATE]);
    $this->getJson("/api/v3/channels/{$channel}/terms")->assertForbidden();
});

it('lists the terms of a studio for its members only', function () {
    [$team, $users] = makeTeam();
    newTerm(makeChannel($team), $users['owner'], ['word' => 'in-channel']);
    $this->postJson("/api/v3/studios/{$team}/terms", ['word' => 'general', 'meaning' => 'g', 'language' => 'zh-Hans'], authHeader($users['owner']))->assertCreated();

    $this->getJson("/api/v3/studios/{$team}/terms?order=word", authHeader($users['reader']))
        ->assertOk()->assertJsonPath('data.*.word', ['general', 'in-channel']);
    $this->getJson("/api/v3/studios/{$team}/terms?general=1", authHeader($users['reader']))
        ->assertJsonPath('data.*.word', ['general']);
    $this->getJson("/api/v3/studios/{$team}/terms?general=true", authHeader($users['reader']))
        ->assertJsonPath('data.*.word', ['general']);
    $this->getJson("/api/v3/studios/{$team}/terms?general=false&order=word", authHeader($users['reader']))
        ->assertJsonPath('data.*.word', ['general', 'in-channel']);
    $this->getJson("/api/v3/studios/{$team}/terms", authHeader($users['outsider']))->assertForbidden();
    $this->getJson("/api/v3/studios/{$team}/terms")->assertUnauthorized();
});

it('looks terms up by word across visible channels only', function () {
    $owner = makeStudio('owner');
    $public = makeChannel($owner, 'public');
    $private = makeChannel($owner, 'private');
    Channel::where('uid', $private)->update(['status' => Channel::STATUS_PRIVATE]);
    newTerm($public, $owner, ['word' => 'citta', 'meaning' => '心']);
    newTerm($private, $owner, ['word' => 'citta', 'meaning' => '心（私有）']);
    newTerm($public, $owner, ['word' => 'citta', 'tag' => '十七特征', 'meaning' => '心（特征）']);

    $this->getJson('/api/v3/terms?word=citta')->assertOk()->assertJsonPath('meta.total', 2);
    $this->getJson('/api/v3/terms?word=citta', authHeader($owner))->assertJsonPath('meta.total', 3);
    $this->getJson('/api/v3/terms?word=citta&tag='.urlencode('十七特征'))->assertJsonPath('data.*.meaning', ['心（特征）']);
    $this->getJson('/api/v3/terms?word=citta&tag=')->assertJsonPath('data.*.meaning', ['心']);
    $this->getJson('/api/v3/terms')->assertUnprocessable()->assertJsonValidationErrors('word');

    // 默认不带正文；with_body 时每条都带，并带上 channel 名字
    expect($this->getJson('/api/v3/terms?word=citta')->json('data.0'))->not->toHaveKey('body');
    // 查询串里的 true / false（v6 发的就是它们），不能 422，false 也不能当真
    expect($this->getJson('/api/v3/terms?word=citta&with_body=false')->assertOk()->json('data.0'))->not->toHaveKey('body');
    $this->getJson('/api/v3/terms?word=citta&with_body=true')->assertOk()->assertJsonPath('data.0.body', fn ($body) => is_array($body) && $body !== []);
    $this->getJson('/api/v3/terms?word=citta&with_body=1')
        ->assertJsonPath('data.0.body', fn ($body) => is_array($body) && $body !== [])
        ->assertJsonPath('data.0.channel.name', 'public');
});

it('loads a page of terms with a constant number of queries', function () {
    $owner = makeStudio('owner');
    $count = function (int $n) use ($owner) {
        $channel = makeChannel($owner, "c{$n}");
        foreach (range(1, $n) as $i) {
            newTerm($channel, $owner, ['word' => "w{$i}"]);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/channels/{$channel}/terms", authHeader($owner))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queries;
    };

    expect($count(8))->toBe($count(2));
});

/* ------------------------------------------------------------------ 改：乐观锁、word/tag 不可改 */

it('requires If-Match, rejects a stale one, and bumps the version', function () {
    $owner = makeStudio('owner');
    $term = newTerm(makeChannel($owner), $owner);
    $url = "/api/v3/terms/{$term['id']}";

    $this->patchJson($url, ['meaning' => '新释义'], authHeader($owner))->assertStatus(428);
    $this->patchJson($url, ['meaning' => '新释义'], authHeader($owner) + ['If-Match' => '"9.9"'])
        ->assertStatus(412)->assertJsonPath('etag', $term['etag']);

    $updated = $this->patchJson($url, ['meaning' => '新释义'], authHeader($owner) + ['If-Match' => $term['etag']])
        ->assertOk()->assertJsonPath('data.meaning', '新释义')->json('data');
    expect($updated['etag'])->not->toBe($term['etag']);
    expect(DhammaTerm::find($term['id'])->version)->toBe(2);

    // 用旧的 etag 再改一次：412
    $this->patchJson($url, ['meaning' => '再改'], authHeader($owner) + ['If-Match' => $term['etag']])->assertStatus(412);
});

it('notices changes made through v2, which only touch modify_time', function () {
    $owner = makeStudio('owner');
    $term = newTerm(makeChannel($owner), $owner);
    DhammaTerm::where('guid', $term['id'])->update(['modify_time' => DB::raw('modify_time + 1'), 'meaning' => 'v2 改的']);

    $this->patchJson("/api/v3/terms/{$term['id']}", ['meaning' => 'v3'], authHeader($owner) + ['If-Match' => $term['etag']])
        ->assertStatus(412);
});

it('never lets word or tag change, and re-extracts categories when the note changes', function () {
    $owner = makeStudio('owner');
    $term = newTerm(makeChannel($owner), $owner);
    $url = "/api/v3/terms/{$term['id']}";

    $this->patchJson($url, ['word' => 'other'], authHeader($owner) + ['If-Match' => $term['etag']])
        ->assertUnprocessable()->assertJsonValidationErrors('word');
    $this->patchJson($url, ['tag' => 'x'], authHeader($owner) + ['If-Match' => $term['etag']])
        ->assertUnprocessable()->assertJsonValidationErrors('tag');

    $this->patchJson($url, ['note' => '{{category|律学}}', 'redirect' => 'dhamma@新义'], authHeader($owner) + ['If-Match' => $term['etag']])
        ->assertOk()
        ->assertJsonPath('data.categories', ['律学'])
        ->assertJsonPath('data.redirect', 'dhamma@新义');
    expect(TermCategory::where('term_guid', $term['id'])->pluck('category')->all())->toBe(['律学']);
});

it('keeps the language of a channel term tied to the channel', function () {
    $owner = makeStudio('owner');
    $term = newTerm(makeChannel($owner), $owner);

    $this->patchJson("/api/v3/terms/{$term['id']}", ['language' => 'en'], authHeader($owner) + ['If-Match' => $term['etag']])
        ->assertUnprocessable()->assertJsonValidationErrors('language');
});

/* ------------------------------------------------------------------ agent 代写 */

it('records the grantor as editor and the model as co-editor, and clears it when a person edits', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();
    $grant = termGrant($human, $model, $channel);

    $term = $this->postJson("/api/v3/channels/{$channel}/terms", ['word' => 'kamma', 'meaning' => '业'], modelAuth($model, $grant))
        ->assertCreated()
        ->assertJsonPath('data.editor.id', $human)
        ->assertJsonPath('data.co_editor.id', $model->uid)
        ->json('data');

    $edited = $this->patchJson("/api/v3/terms/{$term['id']}", ['meaning' => '业力'], modelAuth($model, $grant) + ['If-Match' => $term['etag']])
        ->assertOk()->assertJsonPath('data.co_editor.id', $model->uid)->json('data');
    $this->patchJson("/api/v3/terms/{$term['id']}", ['meaning' => '業'], authHeader($human) + ['If-Match' => $edited['etag']])
        ->assertOk()->assertJsonPath('data.co_editor', null);
});

it('rejects agent writes without a covering grant, studio-level agent writes, and agent deletes', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $other = makeChannel($human, 'other');
    $model = AiModel::factory()->ownedBy($human)->create();
    $body = ['word' => 'kamma', 'meaning' => '业'];

    $this->postJson("/api/v3/channels/{$channel}/terms", $body, modelAuth($model))->assertForbidden();
    $this->postJson("/api/v3/channels/{$channel}/terms", $body, modelAuth($model, termGrant($human, $model, $other)))->assertForbidden();
    // 按 book 限定的授权不覆盖术语（术语不属于某本书）
    $this->postJson("/api/v3/channels/{$channel}/terms", $body, modelAuth($model, termGrant($human, $model, $channel, 3)))->assertForbidden();
    $this->postJson("/api/v3/studios/{$human}/terms", $body + ['language' => 'zh-Hans'], modelAuth($model, termGrant($human, $model, $channel)))
        ->assertForbidden();

    $term = newTerm($channel, $human);
    $this->deleteJson("/api/v3/terms/{$term['id']}", [], modelAuth($model))->assertForbidden();
});

/* ------------------------------------------------------------------ 删 */

it('soft deletes for the owner or team owner only', function () {
    [$team, $users] = makeTeam();
    $term = newTerm(makeChannel($team), $users['editor']);
    $url = "/api/v3/terms/{$term['id']}";

    $this->deleteJson($url, [], authHeader($users['editor']))->assertForbidden();
    $this->deleteJson($url, [], authHeader($users['manager']))->assertForbidden();
    $this->deleteJson($url, [], authHeader($users['owner']))
        ->assertOk()->assertJsonPath('data.id', $term['id'])
        ->assertJsonPath('data.deleted_at', fn ($at) => $at !== null);

    $this->assertSoftDeleted('dhamma_terms', ['guid' => $term['id']]);
    $this->getJson($url)->assertNotFound();
});
