<?php

use App\Models\Discussion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('stores the annotation selector fields on create', function () {
    $userUid = makeStudio('annotator');
    $resId = (string) Str::uuid();

    $response = $this->postJson('/api/v2/discussion', [
        'res_id' => $resId,
        'res_type' => 'sentence',
        'type' => 'note',
        'title' => '义注',
        'content' => '{{1-2-3-4}}',
        'pos_start' => 10,
        'pos_end' => 25,
        'quote_exact' => '被锚定文本',
        'quote_prefix' => '前缀上下文',
        'quote_suffix' => '后缀上下文',
        'notification' => false,
    ], authHeader($userUid))->assertOk();

    $saved = Discussion::where('editor_uid', $userUid)->first();

    expect($saved->pos_start)->toBe(10);
    expect($saved->pos_end)->toBe(25);
    expect($saved->quote_exact)->toBe('被锚定文本');
    expect($saved->quote_prefix)->toBe('前缀上下文');
    expect($saved->quote_suffix)->toBe('后缀上下文');

    // 资源输出同样暴露这些字段
    expect($response->json('data.pos_start'))->toBe(10);
    expect($response->json('data.pos_end'))->toBe(25);
    expect($response->json('data.quote_exact'))->toBe('被锚定文本');
    expect($response->json('data.quote_prefix'))->toBe('前缀上下文');
    expect($response->json('data.quote_suffix'))->toBe('后缀上下文');
});

it('leaves annotation selector fields null when not provided', function () {
    $userUid = makeStudio('annotator');

    $this->postJson('/api/v2/discussion', [
        'res_id' => (string) Str::uuid(),
        'res_type' => 'sentence',
        'type' => 'note',
        'title' => '无锚点',
        'content' => '{{1-2-3-4}}',
        'notification' => false,
    ], authHeader($userUid))->assertOk();

    $saved = Discussion::where('editor_uid', $userUid)->first();

    expect($saved->pos_start)->toBeNull();
    expect($saved->pos_end)->toBeNull();
    expect($saved->quote_exact)->toBeNull();
    expect($saved->quote_prefix)->toBeNull();
    expect($saved->quote_suffix)->toBeNull();
});

it('rejects an invalid annotation selector', function () {
    $userUid = makeStudio('annotator');

    $this->postJson('/api/v2/discussion', [
        'res_id' => (string) Str::uuid(),
        'res_type' => 'sentence',
        'title' => 'x',
        'pos_start' => -1,
        'notification' => false,
    ], authHeader($userUid))->assertStatus(422);

    expect(Discussion::count())->toBe(0);
});

it('updates only the annotation fields present in the request', function () {
    $userUid = makeStudio('annotator');
    $discussion = new Discussion;
    $discussion->forceFill([
        'res_id' => (string) Str::uuid(),
        'res_type' => 'sentence',
        'type' => 'note',
        'title' => '旧标题',
        'content' => '{{1-2-3-4}}',
        'editor_uid' => $userUid,
        'pos_start' => 1,
        'pos_end' => 2,
        'quote_exact' => '保留我',
        'quote_prefix' => '保留前缀',
        'quote_suffix' => '保留后缀',
    ])->save();

    $this->putJson("/api/v2/discussion/{$discussion->id}", [
        'pos_start' => 5,
        'quote_exact' => '新摘录',
    ], authHeader($userUid))->assertOk();

    $discussion->refresh();

    // 只改提交的字段
    expect($discussion->pos_start)->toBe(5);
    expect($discussion->quote_exact)->toBe('新摘录');
    // 未提交的字段原样保留
    expect($discussion->pos_end)->toBe(2);
    expect($discussion->quote_prefix)->toBe('保留前缀');
    expect($discussion->quote_suffix)->toBe('保留后缀');
});

it('allows clearing an annotation field explicitly', function () {
    $userUid = makeStudio('annotator');
    $discussion = new Discussion;
    $discussion->forceFill([
        'res_id' => (string) Str::uuid(),
        'res_type' => 'sentence',
        'type' => 'note',
        'title' => '旧标题',
        'editor_uid' => $userUid,
        'quote_exact' => '旧摘录',
    ])->save();

    $this->putJson("/api/v2/discussion/{$discussion->id}", [
        'quote_exact' => null,
    ], authHeader($userUid))->assertOk();

    expect($discussion->refresh()->quote_exact)->toBeNull();
});
