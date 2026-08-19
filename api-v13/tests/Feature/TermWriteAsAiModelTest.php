<?php

use App\Models\AiModel;
use App\Models\DhammaTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * 术语的写入链路与句子一致：人类签出 channel 的 access token，AI 模型带着
 * 它以自己的身份建/改术语。这里的核心同样是署名——editor_uid 必须是模型。
 */

/** 人类签出 channel access token，并取出模型身份 token。 */
function termWriteTokens($test, string $human, string $channel, AiModel $model): array
{
    $accessToken = $test->postJson('/api/v2/access-token', [
        'payload' => [[
            'res_type' => 'channel',
            'res_id' => $channel,
            'power' => 'edit',
            'book' => 0,
        ]],
    ], authHeader($human))
        ->assertOk()
        ->assertJsonPath('data.count', 1)
        ->json('data.rows.0.token');

    $modelToken = $test->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->assertOk()
        ->json('data.token');

    return [$accessToken, $modelToken];
}

it('creates a channel term attributed to the ai model', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create(['name' => 'claude-opus-5']);
    [$accessToken, $modelToken] = termWriteTokens($this, $human, $channel, $model);

    $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'other_meaning' => '念住',
        'note' => '四念处之念处。',
        'tag' => 'abhidhamma',
        'channel' => $channel,
        'access_token' => $accessToken,
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertOk()
        ->assertJsonPath('data.word', 'satipaṭṭhāna');

    $saved = DhammaTerm::where('word', 'satipaṭṭhāna')->first();

    expect($saved)->not->toBeNull();
    expect($saved->meaning)->toBe('念处');
    expect($saved->channal)->toBe($channel);
    // 核心断言：署名归模型，而不是发起操作的人类
    expect($saved->editor_uid)->toBe($model->uid);
    expect($saved->editor_uid)->not->toBe($human);
    // owner 仍是 channel 所属 studio
    expect($saved->owner)->toBe($human);
});

it('refuses to create a term without an access token', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();

    $modelToken = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->json('data.token');

    $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'channel' => $channel,
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertStatus(403);

    expect(DhammaTerm::count())->toBe(0);
});

it('refuses an access token issued for a different channel', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human, 'mine');
    $otherChannel = makeChannel(makeStudio('someone-else'), 'not mine');
    $model = AiModel::factory()->ownedBy($human)->create();
    [$accessToken, $modelToken] = termWriteTokens($this, $human, $channel, $model);

    $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'channel' => $otherChannel,
        'access_token' => $accessToken,
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertStatus(403);

    expect(DhammaTerm::count())->toBe(0);
});

it('updates a term as the ai model, leaving unsubmitted fields untouched', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();
    [$accessToken, $modelToken] = termWriteTokens($this, $human, $channel, $model);

    $guid = $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'note' => '原有的注解',
        'tag' => 'abhidhamma',
        'channel' => $channel,
        'access_token' => $accessToken,
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertOk()
        ->json('data.guid');

    $createTime = DhammaTerm::find($guid)->create_time;

    // 只提交 meaning：note/tag 必须原样保留
    $this->putJson("/api/v2/terms/{$guid}", [
        'meaning' => '念住',
        'access_token' => $accessToken,
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertOk()
        ->assertJsonPath('data.meaning', '念住');

    $saved = DhammaTerm::find($guid);

    expect($saved->meaning)->toBe('念住');
    expect($saved->word)->toBe('satipaṭṭhāna');
    expect($saved->note)->toBe('原有的注解');
    expect($saved->tag)->toBe('abhidhamma');
    expect($saved->editor_uid)->toBe($model->uid);
    // create_time 是创建时刻，改动不该刷新它
    expect($saved->create_time)->toBe($createTime);
});

it('refuses to update a term with no access token', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();
    [$accessToken, $modelToken] = termWriteTokens($this, $human, $channel, $model);

    $guid = $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'channel' => $channel,
        'access_token' => $accessToken,
    ], ['Authorization' => 'Bearer '.$modelToken])->json('data.guid');

    $this->putJson("/api/v2/terms/{$guid}", [
        'meaning' => '篡改',
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertStatus(403);

    expect(DhammaTerm::find($guid)->meaning)->toBe('念处');
});

it('refuses a studio term written into someone elses studio', function () {
    $human = makeStudio('tester');
    $victim = makeStudio('victim');

    // 人类身份，但把 owner 指向别人的 studio
    $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'studioName' => 'victim',
        'language' => 'zh-Hans',
    ], authHeader($human))
        ->assertStatus(403);

    expect(DhammaTerm::count())->toBe(0);

    // 自己的 studio 则放行
    $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'studioName' => 'tester',
        'language' => 'zh-Hans',
    ], authHeader($human))->assertOk();

    expect(DhammaTerm::where('owner', $human)->count())->toBe(1);
});

it('refuses to create a studio-level term as an ai model', function () {
    $human = makeStudio('tester');
    $model = AiModel::factory()->ownedBy($human)->create();

    $modelToken = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->json('data.token');

    // 不给 channel：模型没有任何可核验的授权，必须被拒
    $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'studioName' => 'tester',
        'language' => 'zh-Hans',
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertStatus(403);

    expect(DhammaTerm::count())->toBe(0);
});

it('refuses to edit a studio-level term as an ai model', function () {
    $human = makeStudio('tester');
    $model = AiModel::factory()->ownedBy($human)->create();

    // 人类先建一条 studio 级术语
    $guid = $this->postJson('/api/v2/terms', [
        'word' => 'satipaṭṭhāna',
        'meaning' => '念处',
        'studioName' => 'tester',
        'language' => 'zh-Hans',
    ], authHeader($human))->assertOk()->json('data.guid');

    $modelToken = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->json('data.token');

    $this->putJson("/api/v2/terms/{$guid}", [
        'meaning' => '篡改',
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertStatus(403);

    expect(DhammaTerm::find($guid)->meaning)->toBe('念处');
});
