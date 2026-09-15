<?php

use App\Models\AiModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('stores every field the client sends', function () {
    $ownerId = makeStudio('tester');

    $this->postJson('/api/v2/ai-model', [
        'studio_name' => 'tester',
        'name' => 'claude-opus-5',
        'model' => 'claude-opus-5-20260101',
        'url' => 'https://api.anthropic.com',
        'key' => 'sk-ant-test',
        'privacy' => 'public',
        'description' => 'writes sentences',
    ], authHeader($ownerId))->assertOk();

    $saved = AiModel::where('owner_id', $ownerId)->where('name', 'claude-opus-5')->first();

    // 修复前 store() 只写 name/uid/real_name/owner_id/editor_id，其余全丢
    expect($saved->model)->toBe('claude-opus-5-20260101');
    expect($saved->url)->toBe('https://api.anthropic.com');
    expect($saved->key)->toBe('sk-ant-test');
    expect($saved->privacy)->toBe('public');
    expect($saved->description)->toBe('writes sentences');
});

it('defaults privacy to private', function () {
    $ownerId = makeStudio('tester');

    $this->postJson('/api/v2/ai-model', [
        'studio_name' => 'tester',
        'name' => 'no-privacy-given',
    ], authHeader($ownerId))->assertOk();

    expect(AiModel::where('owner_id', $ownerId)->first()->privacy)->toBe('private');
});

it('refuses a duplicate name inside the same studio', function () {
    $ownerId = makeStudio('tester');
    AiModel::factory()->ownedBy($ownerId)->create(['name' => 'claude-opus-5']);

    $this->postJson('/api/v2/ai-model', [
        'studio_name' => 'tester',
        'name' => 'claude-opus-5',
    ], authHeader($ownerId))->assertStatus(409);

    expect(AiModel::where('owner_id', $ownerId)->count())->toBe(1);
});

it('requires a name', function () {
    $ownerId = makeStudio('tester');

    $this->postJson('/api/v2/ai-model', [
        'studio_name' => 'tester',
    ], authHeader($ownerId))->assertStatus(422);
});

it('updates only the fields present in the request', function () {
    $owner = (string) Str::uuid();
    $model = AiModel::factory()->ownedBy($owner)->create([
        'name' => 'claude-opus-5',
        'key' => 'sk-keep-me',
        'system_prompt' => 'keep me too',
        'url' => 'https://api.anthropic.com',
    ]);

    // 只改 model 一个字段——这正是 Skill 的 ensure-model 会发的局部 PUT
    $this->putJson("/api/v2/ai-model/{$model->uid}", [
        'model' => 'claude-opus-5-20260101',
    ], authHeader($owner))->assertOk();

    $model->refresh();

    expect($model->model)->toBe('claude-opus-5-20260101');
    // 修复前这些字段会被 input() 的 null 覆盖掉
    expect($model->key)->toBe('sk-keep-me');
    expect($model->system_prompt)->toBe('keep me too');
    expect($model->url)->toBe('https://api.anthropic.com');
    expect($model->name)->toBe('claude-opus-5');
});

it('still allows clearing a field explicitly', function () {
    $owner = (string) Str::uuid();
    $model = AiModel::factory()->ownedBy($owner)->create(['description' => 'old text']);

    $this->putJson("/api/v2/ai-model/{$model->uid}", [
        'description' => null,
    ], authHeader($owner))->assertOk();

    expect($model->refresh()->description)->toBeNull();
});

it('refuses to rename onto an existing name', function () {
    $owner = (string) Str::uuid();
    AiModel::factory()->ownedBy($owner)->create(['name' => 'taken']);
    $model = AiModel::factory()->ownedBy($owner)->create(['name' => 'mine']);

    $this->putJson("/api/v2/ai-model/{$model->uid}", [
        'name' => 'taken',
    ], authHeader($owner))->assertStatus(409);

    expect($model->refresh()->name)->toBe('mine');
});

it('rejects an update from a non-owner', function () {
    $model = AiModel::factory()->create(['name' => 'untouched']);

    $this->putJson("/api/v2/ai-model/{$model->uid}", [
        'name' => 'hijacked',
    ], authHeader((string) Str::uuid()))->assertStatus(403);

    expect($model->refresh()->name)->toBe('untouched');
});
