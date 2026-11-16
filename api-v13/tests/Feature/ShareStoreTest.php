<?php

use App\Models\AiModel;
use App\Models\Article;
use App\Models\Project;
use App\Models\Share;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * POST /v2/share（添加协作者）的快照测试。
 *
 * 原实现不校验登录也不校验资源归属，任何人都能给任意资源签 editor（power 20），
 * 再凭它改别人的译文。修复只收紧「谁能调」，拥有者的请求与响应形状保持不变——
 * v4 的 CollaboratorAdd 还在调它。
 */
function shareChannelPayload(string $resId, array $userIds, string $role = 'editor', int $resType = 2): array
{
    return [
        'res_id' => $resId,
        'res_type' => $resType,
        'user_id' => $userIds,
        'user_type' => 'user',
        'role' => $role,
    ];
}

function makeShareArticle(string $ownerUid): string
{
    $uid = (string) Str::uuid();
    (new Article)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => $uid,
        'title' => 'article',
        'owner' => $ownerUid,
        'owner_id' => 0,
        'editor_id' => 0,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();

    return $uid;
}

function makeShareProject(string $ownerUid): string
{
    $uid = (string) Str::uuid();
    (new Project)->forceFill([
        'uid' => $uid,
        'title' => 'project',
        'owner_id' => $ownerUid,
        'editor_id' => $ownerUid,
    ])->save();

    return $uid;
}

function makeShareAiModel(string $ownerUid): string
{
    $uid = (string) Str::uuid();
    (new AiModel)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => $uid,
        'name' => 'model',
        'real_name' => 'model-'.Str::random(6),
        'model' => 'gpt-4',
        'owner_id' => $ownerUid,
        'editor_id' => $ownerUid,
    ])->save();

    return $uid;
}

it('lets the channel owner add an editor', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $friend = makeStudio('friend');

    $this->postJson('/api/v2/share', shareChannelPayload($channel, [$friend]), authHeader($owner))
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('data', 1);

    $row = Share::where('res_id', $channel)->where('cooperator_id', $friend)->first();
    expect($row)->not->toBeNull();
    expect((int) $row->power)->toBe(20);
    expect((int) $row->cooperator_type)->toBe(0);
});

it('maps the reader role to power 10', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $friend = makeStudio('friend');

    $this->postJson('/api/v2/share', shareChannelPayload($channel, [$friend], 'reader'), authHeader($owner))
        ->assertJsonPath('ok', true);

    expect((int) Share::where('res_id', $channel)->value('power'))->toBe(10);
});

it('lets owners share articles, projects and ai models', function (int $resType, Closure $make) {
    $owner = makeStudio('owner');
    $resId = $make($owner);
    $friend = makeStudio('friend');

    $this->postJson('/api/v2/share', shareChannelPayload($resId, [$friend], 'editor', $resType), authHeader($owner))
        ->assertJsonPath('ok', true);

    expect(Share::where('res_id', $resId)->where('cooperator_id', $friend)->exists())->toBeTrue();
})->with([
    'article' => [3, fn (string $owner) => makeShareArticle($owner)],
    'project' => [7, fn (string $owner) => makeShareProject($owner)],
    'ai model' => [8, fn (string $owner) => makeShareAiModel($owner)],
]);

it('rejects anonymous requests', function () {
    $channel = makeChannel(makeStudio('owner'));
    $attacker = makeStudio('attacker');

    $this->postJson('/api/v2/share', shareChannelPayload($channel, [$attacker]))
        ->assertStatus(401)
        ->assertJsonPath('ok', false);

    expect(Share::where('res_id', $channel)->exists())->toBeFalse();
});

it('rejects users who do not own the resource', function () {
    $channel = makeChannel(makeStudio('owner'));
    $attacker = makeStudio('attacker');

    $this->postJson('/api/v2/share', shareChannelPayload($channel, [$attacker]), authHeader($attacker))
        ->assertStatus(403)
        ->assertJsonPath('ok', false);

    expect(Share::where('res_id', $channel)->exists())->toBeFalse();
});

it('rejects a wrong res_type that would hide the real owner', function () {
    $owner = makeStudio('owner');
    $article = makeShareArticle($owner);
    $attacker = makeStudio('attacker');

    // 资源是 article，attacker 却谎称它是自己名下的 channel
    $this->postJson('/api/v2/share', shareChannelPayload($article, [$attacker], 'editor', 2), authHeader($attacker))
        ->assertStatus(403);

    expect(Share::where('res_id', $article)->exists())->toBeFalse();
});

it('does not let an editor collaborator add more collaborators', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $editor = makeStudio('editor');
    $outsider = makeStudio('outsider');

    $this->postJson('/api/v2/share', shareChannelPayload($channel, [$editor]), authHeader($owner))
        ->assertJsonPath('ok', true);

    $this->postJson('/api/v2/share', shareChannelPayload($channel, [$outsider]), authHeader($editor))
        ->assertStatus(403);

    expect(Share::where('res_id', $channel)->where('cooperator_id', $outsider)->exists())->toBeFalse();
});
