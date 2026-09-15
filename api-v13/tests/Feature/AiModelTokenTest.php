<?php

use App\Models\AiModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('rejects an anonymous request', function () {
    $model = AiModel::factory()->create();

    $this->getJson("/api/v2/ai-model-token/{$model->uid}")
        ->assertStatus(401);
});

it('rejects a user who does not own the model', function () {
    $model = AiModel::factory()->create();

    $this->getJson(
        "/api/v2/ai-model-token/{$model->uid}",
        authHeader((string) Str::uuid())
    )->assertStatus(403);
});

it('issues a token to the owner', function () {
    $owner = (string) Str::uuid();
    $model = AiModel::factory()->ownedBy($owner)->create(['name' => 'claude-opus-5']);

    $response = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.uid', $model->uid)
        ->assertJsonPath('data.name', 'claude-opus-5');

    // 关键断言：签出的 token 代表「模型」而非发起请求的用户。
    // 用它写句子时，editor_uid 才会记成模型 uid。
    $jwt = JWT::decode(
        $response->json('data.token'),
        new Key(config('mint.app.jwt_secrets_key'), 'HS512')
    );
    expect($jwt->uid)->toBe($model->uid);
    expect($jwt->exp)->toBeGreaterThan(time());
});

it('issues a model token that expires in 30 days', function () {
    $owner = (string) Str::uuid();
    $model = AiModel::factory()->ownedBy($owner)->create();

    $response = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($owner))
        ->assertOk();

    $jwt = decodeToken($response->json('data.token'));

    // 30 天，不是人类登录 token 的 365 天
    expect($jwt->exp - time())->toBeLessThanOrEqual(60 * 60 * 24 * 30)
        ->and($jwt->exp - time())->toBeGreaterThan(60 * 60 * 24 * 29);
    expect($jwt->typ)->toBe('ai-model');
    expect($jwt->ver)->toBe(1);
});

it('404s on an unknown model', function () {
    $this->getJson(
        '/api/v2/ai-model-token/'.Str::uuid(),
        authHeader((string) Str::uuid())
    )->assertStatus(404);
});

it('rejects an anonymous revoke', function () {
    $model = AiModel::factory()->create();

    $this->deleteJson("/api/v2/ai-model-token/{$model->uid}")
        ->assertStatus(401);
});

it('rejects a revoke from a user who does not own the model', function () {
    $model = AiModel::factory()->create();

    $this->deleteJson(
        "/api/v2/ai-model-token/{$model->uid}",
        [],
        authHeader((string) Str::uuid())
    )->assertStatus(403);

    expect(AiModel::where('uid', $model->uid)->value('token_version'))->toBe(1);
});

it('invalidates issued tokens when the owner revokes them', function () {
    $owner = (string) Str::uuid();
    $model = AiModel::factory()->ownedBy($owner)->create();

    $token = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($owner))
        ->assertOk()
        ->json('data.token');

    expect(currentUid($token))->toBe($model->uid);

    $this->deleteJson("/api/v2/ai-model-token/{$model->uid}", [], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.token_version', 2);

    // 撤销后旧 token 立刻失效，尽管它的 exp 还在 30 天后
    expect(currentUid($token))->toBeFalse();

    // 重新签发的 token 带新版本号，可用
    $fresh = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($owner))
        ->json('data.token');
    expect(decodeToken($fresh)->ver)->toBe(2);
    expect(currentUid($fresh))->toBe($model->uid);
});

it('rejects a model token whose model has been deleted', function () {
    $owner = (string) Str::uuid();
    $model = AiModel::factory()->ownedBy($owner)->create();

    $token = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($owner))
        ->json('data.token');

    AiModel::where('uid', $model->uid)->delete();

    expect(currentUid($token))->toBeFalse();
});

it('rejects pre-versioning model tokens', function () {
    // 引入 token_version 之前签出的模型 token：没有 typ/ver，id 恒为 0，无法撤销
    $model = AiModel::factory()->create();
    $legacy = JWT::encode([
        'nbf' => time(),
        'exp' => time() + 3600,
        'uid' => $model->uid,
        'id' => 0,
    ], config('mint.app.jwt_secrets_key'), 'HS512');

    expect(currentUid($legacy))->toBeFalse();
});

it('leaves human tokens alone', function () {
    expect(currentUid(userToken('a-user-uid', 42)))->toBe('a-user-uid');
});
