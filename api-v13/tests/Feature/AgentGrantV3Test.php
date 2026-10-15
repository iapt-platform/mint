<?php

use App\Models\AiModel;
use App\Models\Discussion;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * agent 协作：人类签发授权（POST /v3/agent-grants），模型凭模型 token + X-Agent-Grant 代写。
 * 代写的记录 editor = 授权人、co_editor = 模型，权限按授权人判、范围限授权的 channel / book。
 */

/** 模型身份的请求头；$grant 给了就带上授权 */
function modelHeader(AiModel $model, ?string $grant = null): array
{
    $headers = ['Authorization' => 'Bearer '.AuthService::getUserToken($model->uid)];
    if ($grant !== null) {
        $headers['X-Agent-Grant'] = $grant;
    }

    return $headers;
}

/** 人类为自己的模型在 channel 上签一张授权，返回 token */
function grantFor(string $human, AiModel $model, string $channel, int $book = 0): string
{
    return test()->postJson('/api/v3/agent-grants', [
        'ai_model' => $model->uid, 'target_type' => 'channel', 'target_id' => $channel, 'book' => $book,
    ], authHeader($human))->assertCreated()->json('data.token');
}

/* ------------------------------------------------------------------ 签发 */

it('issues a grant bound to the model, the channel and the grantor', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();

    $this->postJson('/api/v3/agent-grants', [
        'ai_model' => $model->uid, 'target_type' => 'channel', 'target_id' => $channel, 'book' => 3,
    ], authHeader($human))
        ->assertCreated()
        ->assertJsonPath('data.grantor', $human)
        ->assertJsonPath('data.ai_model', $model->uid)
        ->assertJsonPath('data.target_id', $channel)
        ->assertJsonPath('data.book', 3)
        ->assertJsonStructure(['data' => ['token', 'expires_at']]);
});

it('refuses to issue a grant without the right to do so', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();
    $body = ['ai_model' => $model->uid, 'target_type' => 'channel', 'target_id' => $channel];

    $this->postJson('/api/v3/agent-grants', $body)->assertUnauthorized();
    $this->postJson('/api/v3/agent-grants', $body, modelHeader($model))->assertForbidden();           // 模型不能给自己授权
    $this->postJson('/api/v3/agent-grants', $body, authHeader(makeStudio('stranger')))->assertForbidden(); // 模型不是他的
    $other = makeChannel(makeStudio('other'));
    $this->postJson('/api/v3/agent-grants', [...$body, 'target_id' => $other], authHeader($human))->assertForbidden(); // channel 编不了
    $this->postJson('/api/v3/agent-grants', [...$body, 'ai_model' => (string) Str::uuid()], authHeader($human))
        ->assertStatus(422)->assertJsonValidationErrors('ai_model');
    $this->postJson('/api/v3/agent-grants', [...$body, 'target_type' => 'article'], authHeader($human))
        ->assertStatus(422)->assertJsonValidationErrors('target_type');
});

/* ------------------------------------------------------------------ 代写 */

it('records the grantor as editor and the model as co-editor', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $sentence = makeSentence($channel, 1, 1, 1, 'text')->uid;
    $model = AiModel::factory()->ownedBy($human)->create();
    $grant = grantFor($human, $model, $channel);

    // 批注：只有句子的管理者能建——按授权人判，所以模型能写进来
    $note = $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence, 'type' => 'commentary', 'content' => '{{1-2-3-4}}',
    ], modelHeader($model, $grant))
        ->assertCreated()
        ->assertJsonPath('data.editor.id', $human)
        ->assertJsonPath('data.co_editor.id', $model->uid)
        ->json('data');

    $this->postJson("/api/v3/discussions/{$note['id']}/replies", ['content' => '补充', 'notify' => false], modelHeader($model, $grant))
        ->assertCreated()
        ->assertJsonPath('data.co_editor.id', $model->uid);
    $this->patchJson("/api/v3/discussions/{$note['id']}", ['pos_end' => 2], modelHeader($model, $grant))->assertOk();

    expect(Discussion::find($note['id']))
        ->editor_uid->toBe($human)
        ->co_editor_uid->toBe($model->uid);

    // 人类自己写的没有协作者
    $this->getJson("/api/v3/discussions/{$note['id']}")->assertJsonPath('data.co_editor.id', $model->uid);
    $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence, 'title' => 't', 'notify' => false,
    ], authHeader($human))->assertCreated()->assertJsonPath('data.co_editor', null);
});

it('rejects agent writes that are not covered by a valid grant', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $sentence = makeSentence($channel, 1, 1, 1, 'text')->uid;
    $model = AiModel::factory()->ownedBy($human)->create();
    $body = ['target_type' => 'sentence', 'target_id' => $sentence, 'type' => 'note', 'content' => 'x'];

    $this->postJson('/api/v3/discussions', $body, modelHeader($model))->assertForbidden();             // 没授权
    $this->postJson('/api/v3/discussions', $body, modelHeader($model, 'garbage'))->assertForbidden();  // 伪造

    $otherChannel = makeChannel($human, 'other');
    $this->postJson('/api/v3/discussions', $body, modelHeader($model, grantFor($human, $model, $otherChannel)))
        ->assertForbidden();                                                                           // 别的 channel
    $this->postJson('/api/v3/discussions', $body, modelHeader($model, grantFor($human, $model, $channel, 2)))
        ->assertForbidden();                                                                           // 别的 book

    $another = AiModel::factory()->ownedBy($human)->create();
    $this->postJson('/api/v3/discussions', $body, modelHeader($another, grantFor($human, $model, $channel)))
        ->assertForbidden();                                                                           // 授权给的不是它

    // 人类带授权：授权只给模型用
    $this->postJson('/api/v3/discussions', $body, [...authHeader($human), 'X-Agent-Grant' => grantFor($human, $model, $channel)])
        ->assertStatus(422);
});

it('does not accept a v2 access token as a grant', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $sentence = makeSentence($channel, 1, 1, 1, 'text')->uid;
    $model = AiModel::factory()->ownedBy($human)->create();
    // v2 的载荷由客户端决定，伪造 grantor / act 也签得出来
    $v2 = $this->postJson('/api/v2/access-token', ['payload' => [[
        'res_type' => 'channel', 'res_id' => $channel, 'power' => 'edit', 'book' => 0,
        'typ' => 'agent-grant', 'grantor' => $human, 'act' => $model->uid, 'ver' => 0,
    ]]], authHeader($human))->json('data.rows.0.token');

    $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence, 'type' => 'note', 'content' => 'x',
    ], modelHeader($model, $v2))->assertForbidden();
});

it('voids grants when the model token is revoked', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $sentence = makeSentence($channel, 1, 1, 1, 'text')->uid;
    $model = AiModel::factory()->ownedBy($human)->create();
    $grant = grantFor($human, $model, $channel);

    $model->increment('token_version');   // 撤销：v2 DELETE /v2/ai-model-token 做的就是这个

    $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence, 'type' => 'note', 'content' => 'x',
    ], modelHeader($model->fresh(), $grant))->assertForbidden();
});

it('lets the grantor, not the model, own what the model wrote', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $sentence = makeSentence($channel, 1, 1, 1, 'text')->uid;
    $model = AiModel::factory()->ownedBy($human)->create();
    $id = $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence, 'title' => 'by agent', 'notify' => false,
    ], modelHeader($model, grantFor($human, $model, $channel)))->json('data.id');

    $this->getJson("/api/v3/discussions/{$id}", authHeader($human))
        ->assertJsonPath('data.abilities.update', true)
        ->assertJsonPath('data.abilities.delete', true);
    $this->deleteJson("/api/v3/discussions/{$id}", [], authHeader($human))->assertOk();
});

it('clears the co-editor when a person edits the content, but not on a status change', function () {
    $human = makeStudio('owner');
    $channel = makeChannel($human);
    $sentence = makeSentence($channel, 1, 1, 1, 'text')->uid;
    $model = AiModel::factory()->ownedBy($human)->create();
    $grant = grantFor($human, $model, $channel);
    $id = $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $sentence, 'title' => 'by agent', 'notify' => false,
    ], modelHeader($model, $grant))->json('data.id');

    $this->patchJson("/api/v3/discussions/{$id}", ['status' => 'close'], authHeader($human))
        ->assertJsonPath('data.co_editor.id', $model->uid);    // 管理动作，不动署名
    $this->patchJson("/api/v3/discussions/{$id}", ['content' => '人改过'], authHeader($human))
        ->assertJsonPath('data.co_editor', null);              // 人亲自改了内容
    $this->patchJson("/api/v3/discussions/{$id}", ['content' => 'AI 又改'], modelHeader($model, $grant))
        ->assertJsonPath('data.co_editor.id', $model->uid);    // AI 代改，换回模型
});
