<?php

use App\Models\AiModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

const SECRET_KEY = 'sk-super-secret-api-key';
const SECRET_PROMPT = 'you are a very secret assistant';

function modelWithSecrets(string $ownerId): AiModel
{
    return AiModel::factory()->ownedBy($ownerId)->create([
        'name' => 'secret-model',
        'key' => SECRET_KEY,
        'system_prompt' => SECRET_PROMPT,
        'privacy' => 'public',
    ]);
}

it('never exposes the api key to a stranger listing models', function () {
    modelWithSecrets((string) Str::uuid());

    $body = $this->getJson('/api/v2/ai-model?view=all', authHeader((string) Str::uuid()))
        ->assertOk()
        ->getContent();

    expect($body)->not->toContain(SECRET_KEY);
    expect($body)->not->toContain(SECRET_PROMPT);
    // 非敏感字段仍须返回，否则前端列表会空
    expect($body)->toContain('secret-model');
});

it('gives the owner back key and system_prompt so the edit form can prefill', function () {
    $owner = (string) Str::uuid();
    $model = modelWithSecrets($owner);

    $this->getJson("/api/v2/ai-model/{$model->uid}", authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.key', SECRET_KEY)
        ->assertJsonPath('data.system_prompt', SECRET_PROMPT);
});

it('does not leak internal columns', function () {
    $owner = (string) Str::uuid();
    $model = modelWithSecrets($owner);

    $data = $this->getJson("/api/v2/ai-model/{$model->uid}", authHeader($owner))
        ->assertOk()
        ->json('data');

    // real_name 是模型的登录身份标识，id 是自增主键，都不该外露
    expect($data)->not->toHaveKey('real_name');
    expect($data)->not->toHaveKey('id');
});
