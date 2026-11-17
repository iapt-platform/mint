<?php

use App\Models\Channel;
use App\Models\ProgressChapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 channel：公开目录、studio 列表、我能写的、详情、建、改、删。
 *
 * 端点：
 *   GET    /api/v3/channels                       公开目录（可不登录；system=true 只要系统 channel）
 *   GET    /api/v3/channels/editable              我能写的（登录）
 *   GET    /api/v3/channels/{channel}             详情（公开谁都能看；私有看权限）
 *   PATCH  /api/v3/channels/{channel}             改信息
 *   DELETE /api/v3/channels/{channel}             软删（有内容 409）
 *   GET    /api/v3/studios/{studio}/channels      studio（本人或团队）的工作区列表
 *   POST   /api/v3/studios/{studio}/channels      在 studio 名下建
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function privateChannel(string $ownerUid, string $name = 'private channel'): string
{
    $uid = makeChannel($ownerUid, $name);
    Channel::where('uid', $uid)->update(['status' => Channel::STATUS_PRIVATE]);

    return $uid;
}

function channelIds(array $data): array
{
    return array_column($data, 'id');
}

/* ---------------- 详情与权限矩阵 ---------------- */

it('shows public channels to anyone and hides private ones', function () {
    $owner = makeStudio('owner');
    $public = makeChannel($owner, 'pub');
    $private = privateChannel($owner);

    $this->getJson("/api/v3/channels/{$public}")
        ->assertOk()
        ->assertJsonPath('data.id', $public)
        ->assertJsonPath('data.owner.kind', 'user')
        ->assertJsonPath('data.owner.id', $owner)
        ->assertJsonPath('data.abilities', ['read' => true, 'write' => false, 'update' => false, 'delete' => false]);

    $this->getJson("/api/v3/channels/{$private}")->assertForbidden();
    $this->getJson("/api/v3/channels/{$private}", authHeader(makeStudio('stranger')))->assertForbidden();
    $this->getJson("/api/v3/channels/{$private}", authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.abilities', ['read' => true, 'write' => true, 'update' => true, 'delete' => true]);
});

it('derives abilities on a team channel from the team role', function (string $role, array $abilities) {
    [$team, $users] = makeTeam();
    $channel = privateChannel($team, 'team channel');

    $response = $this->getJson("/api/v3/channels/{$channel}", authHeader($users[$role]));
    if ($abilities === []) {
        $response->assertForbidden();

        return;
    }
    $response->assertOk()
        ->assertJsonPath('data.owner.kind', 'team')
        ->assertJsonPath('data.owner.id', $team)
        ->assertJsonPath('data.abilities', $abilities);
})->with([
    'owner' => ['owner', ['read' => true, 'write' => true, 'update' => true, 'delete' => true]],
    'manager' => ['manager', ['read' => true, 'write' => true, 'update' => true, 'delete' => false]],
    'editor' => ['editor', ['read' => true, 'write' => true, 'update' => true, 'delete' => false]],
    'reader' => ['reader', ['read' => true, 'write' => false, 'update' => false, 'delete' => false]],
    'outsider' => ['outsider', []],
]);

it('derives abilities from share power, including shares to my team', function (int $power, bool $viaTeam, array $abilities) {
    $me = makeStudio('me');
    $channel = privateChannel(makeStudio('author'));
    if ($viaTeam) {
        $team = makeGroup(makeStudio('team-owner'));
        addGroupMember($team, $me);
        grantChannelShare($channel, $team, $power, true);
    } else {
        grantChannelShare($channel, $me, $power);
    }

    $this->getJson("/api/v3/channels/{$channel}", authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.abilities', $abilities);
})->with([
    'reader' => [10, false, ['read' => true, 'write' => false, 'update' => false, 'delete' => false]],
    'editor' => [20, false, ['read' => true, 'write' => true, 'update' => false, 'delete' => false]],
    'manager' => [30, false, ['read' => true, 'write' => true, 'update' => true, 'delete' => false]],
    'editor via team' => [20, true, ['read' => true, 'write' => true, 'update' => false, 'delete' => false]],
]);

it('lets anyone read system channels, even private ones', function () {
    $root = makeStudio('root');
    $pali = makeChannel($root, '_System_Pali_VRI_');
    Channel::where('uid', $pali)->update(['is_system' => true, 'status' => Channel::STATUS_PRIVATE]);
    makeSentence($pali, 7, 7, 1, 'namo');

    $this->getJson("/api/v3/channels/{$pali}")->assertOk()->assertJsonPath('data.abilities.read', true);
    expect(channelIds($this->getJson('/api/v3/channels?system=true')->json('data')))->toBe([$pali]);
    expect(channelIds($this->getJson('/api/v3/tipitaka-paragraph-channels/7/7')->json('data')))->toBe([$pali]);
    // 普通公开目录不列私有的系统 channel
    expect(channelIds($this->getJson('/api/v3/channels')->json('data')))->toBe([]);
});

it('never lets anyone update or delete a system channel', function () {
    $owner = makeStudio('root');
    $channel = makeChannel($owner, '_System_Test_');
    Channel::where('uid', $channel)->update(['is_system' => true]);

    $this->getJson("/api/v3/channels/{$channel}", authHeader($owner))
        ->assertJsonPath('data.abilities.update', false)
        ->assertJsonPath('data.abilities.delete', false);
    $this->patchJson("/api/v3/channels/{$channel}", ['name' => 'x'], authHeader($owner))->assertForbidden();
});

/* ---------------- 公开目录 ---------------- */

it('lists public channels only, with optional system filter and search', function () {
    $owner = makeStudio('owner');
    $public = makeChannel($owner, 'Alpha public');
    privateChannel($owner, 'Alpha private');
    $system = makeChannel($owner, 'Alpha system');
    Channel::where('uid', $system)->update(['is_system' => true]);

    expect(channelIds($this->getJson('/api/v3/channels?search=alpha')->assertOk()->json('data')))
        ->toEqualCanonicalizing([$public, $system]);
    expect(channelIds($this->getJson('/api/v3/channels?system=true')->json('data')))->toBe([$system]);
});

it('adds chapter progress when book and para are given, and sorts by it', function () {
    $owner = makeStudio('owner');
    $low = makeChannel($owner, 'low');
    $high = makeChannel($owner, 'high');
    makeChannel($owner, 'none');
    foreach ([[$low, 0.2], [$high, 0.9]] as [$channel, $progress]) {
        (new ProgressChapter)->forceFill([
            'uid' => (string) Str::uuid(), 'book' => 1, 'para' => 10, 'channel_id' => $channel,
            'lang' => 'zh', 'all_trans' => 100, 'public' => 100, 'progress' => $progress,
        ])->save();
    }

    $data = $this->getJson('/api/v3/channels?book=1&para=10&order=progress')->assertOk()->json('data');

    expect(array_slice(channelIds($data), 0, 2))->toBe([$high, $low]);
    expect($data[0]['progress'])->toBe(0.9);
    expect($data[2]['progress'])->toBeNull();
    expect($this->getJson('/api/v3/channels')->json('data.0'))->not->toHaveKey('progress');
});

it('rejects a para without a book', function () {
    $this->getJson('/api/v3/channels?para=3')->assertUnprocessable()->assertJsonValidationErrors('book');
});

it('does not query per channel in lists', function () {
    $me = makeStudio('me');
    $countQueriesFor = function (int $extra) use ($me): int {
        for ($i = 0; $i < $extra; $i++) {
            makeChannel(makeStudio('o-'.Str::random(8)), 'n1 '.Str::random(5));
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v3/channels?search=n1', authHeader($me))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(8))->toBe($countQueriesFor(1));
});

/* ---------------- studio 列表 ---------------- */

it('lists my own and directly shared channels, not those shared to my teams', function () {
    $me = makeStudio('me');
    $mine = privateChannel($me, 'mine');
    $direct = privateChannel(makeStudio('a'), 'direct');
    grantChannelShare($direct, $me, 10);
    $team = makeGroup(makeStudio('team-owner'));
    addGroupMember($team, $me);
    $viaTeam = privateChannel(makeStudio('b'), 'via team');
    grantChannelShare($viaTeam, $team, 20, true);

    $list = fn (string $relation) => channelIds(
        $this->getJson("/api/v3/studios/{$me}/channels?relation={$relation}", authHeader($me))->assertOk()->json('data')
    );

    expect($list('own'))->toBe([$mine]);
    expect($list('shared'))->toBe([$direct]);
    expect($list('all'))->toEqualCanonicalizing([$mine, $direct]);
    $this->getJson("/api/v3/studios/{$me}/channels?relation=shared", authHeader($me))->assertJsonPath('meta.total', 1);
});

it('lists a team\'s own and shared channels in its teamspace', function () {
    [$team, $users] = makeTeam();
    $owned = privateChannel($team, 'team owned');
    $shared = privateChannel(makeStudio('author'), 'shared to team');
    grantChannelShare($shared, $team, 20, true);
    privateChannel($users['reader'], 'reader personal');

    $list = fn (string $relation) => channelIds(
        $this->getJson("/api/v3/studios/{$team}/channels?relation={$relation}", authHeader($users['reader']))->assertOk()->json('data')
    );

    expect($list('own'))->toBe([$owned]);
    expect($list('shared'))->toBe([$shared]);
});

it('filters shared channels by collaborator', function () {
    $me = makeStudio('me');
    $a = makeStudio('a');
    $fromA = privateChannel($a, 'from a');
    $fromB = privateChannel(makeStudio('b'), 'from b');
    grantChannelShare($fromA, $me, 10);
    grantChannelShare($fromB, $me, 10);

    expect(channelIds($this->getJson("/api/v3/studios/{$me}/channels?relation=shared&collaborator={$a}", authHeader($me))->json('data')))
        ->toBe([$fromA]);
});

it('hides a studio list from strangers and requires login', function () {
    [$team, $users] = makeTeam();

    $this->getJson("/api/v3/studios/{$team}/channels", authHeader($users['outsider']))->assertForbidden();
    $this->getJson("/api/v3/studios/{$users['owner']}/channels", authHeader($users['editor']))->assertForbidden();
    $this->getJson("/api/v3/studios/{$team}/channels")->assertUnauthorized();
});

/* ---------------- 新建 ---------------- */

it('creates channels in my studio or in a team I can create in', function (string $role, int $status) {
    [$team, $users] = makeTeam();
    $actor = $users[$role];
    $studio = $role === 'self' ? $actor : $team;

    $response = $this->postJson("/api/v3/studios/{$studio}/channels", [
        'name' => 'new channel', 'type' => 'translation', 'lang' => 'zh-Hans',
    ], authHeader($actor));

    $response->assertStatus($status);
    if ($status === 201) {
        $response->assertJsonPath('data.status', Channel::STATUS_PRIVATE)
            ->assertJsonPath('data.owner.id', $studio)
            ->assertJsonPath('data.abilities.update', true);
        expect(Channel::where('owner_uid', $studio)->where('name', 'new channel')->exists())->toBeTrue();
    }
})->with([
    'team owner' => ['owner', 201],
    'team manager' => ['manager', 201],
    'team editor' => ['editor', 403],
    'team reader' => ['reader', 403],
    'outsider' => ['outsider', 403],
]);

it('creates a channel in my own studio', function () {
    $me = makeStudio('me');

    $this->postJson("/api/v3/studios/{$me}/channels", ['name' => 'x', 'type' => 'translation', 'lang' => 'en'], authHeader($me))
        ->assertCreated()
        ->assertJsonPath('data.owner.kind', 'user');
});

it('rejects duplicate names within a studio and bad input', function () {
    $me = makeStudio('me');
    makeChannel($me, 'dup');

    $this->postJson("/api/v3/studios/{$me}/channels", ['name' => 'dup', 'type' => 'translation', 'lang' => 'en'], authHeader($me))
        ->assertUnprocessable()->assertJsonValidationErrors('name');
    $this->postJson("/api/v3/studios/{$me}/channels", ['name' => 'x', 'type' => 'bogus'], authHeader($me))
        ->assertUnprocessable()->assertJsonValidationErrors(['type', 'lang']);
});

it('does not let AI models create, update or delete channels', function () {
    $owner = makeStudio('owner');
    $model = makeModelAccount($owner);
    $channel = makeChannel($model, 'model owned');

    $this->postJson("/api/v3/studios/{$model}/channels", ['name' => 'x', 'type' => 'translation', 'lang' => 'en'], authHeader($model))
        ->assertForbidden();
    $this->patchJson("/api/v3/channels/{$channel}", ['name' => 'y'], authHeader($model))->assertForbidden();
    $this->deleteJson("/api/v3/channels/{$channel}", [], authHeader($model))->assertForbidden();
});

/* ---------------- 改 ---------------- */

it('updates only the fields sent', function () {
    $me = makeStudio('me');
    $channel = makeChannel($me, 'before');
    Channel::where('uid', $channel)->update(['summary' => 'keep me']);

    $this->patchJson("/api/v3/channels/{$channel}", ['name' => 'after', 'status' => 10], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.name', 'after')
        ->assertJsonPath('data.status', 10)
        ->assertJsonPath('data.summary', 'keep me');
});

it('lets team editors update a team channel but not readers', function () {
    [$team, $users] = makeTeam();
    $channel = privateChannel($team);

    $this->patchJson("/api/v3/channels/{$channel}", ['summary' => 'edited'], authHeader($users['editor']))->assertOk();
    $this->patchJson("/api/v3/channels/{$channel}", ['summary' => 'nope'], authHeader($users['reader']))->assertForbidden();
});

it('does not register PUT on channels', function () {
    $me = makeStudio('me');
    $channel = makeChannel($me);

    $this->putJson("/api/v3/channels/{$channel}", ['name' => 'x'], authHeader($me))->assertMethodNotAllowed();
});

/* ---------------- 删 ---------------- */

it('soft deletes an empty channel and returns it', function () {
    $me = makeStudio('me');
    $channel = makeChannel($me);

    $this->deleteJson("/api/v3/channels/{$channel}", [], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.id', $channel);

    $this->assertSoftDeleted('channels', ['uid' => $channel]);
    $this->getJson("/api/v3/channels/{$channel}")->assertNotFound();
});

it('refuses to delete a channel with translations', function () {
    $me = makeStudio('me');
    $channel = makeChannel($me);
    makeSentence($channel, 1, 1, 1, 'text');

    $this->deleteJson("/api/v3/channels/{$channel}", [], authHeader($me))
        ->assertConflict()
        ->assertJsonPath('content', ['sentence']);
});

it('lets only the team owner delete a team channel', function () {
    [$team, $users] = makeTeam();
    $channel = privateChannel($team);

    $this->deleteJson("/api/v3/channels/{$channel}", [], authHeader($users['manager']))->assertForbidden();
    $this->deleteJson("/api/v3/channels/{$channel}", [], authHeader($users['owner']))->assertOk();
});

/* ---------------- 我能写的 ---------------- */

it('lists channels I can write to across studios, with working search', function () {
    $me = makeStudio('me');
    $mine = privateChannel($me, 'pali mine');
    $editorTeam = makeGroup(makeStudio('t1'));
    addGroupMember($editorTeam, $me, 2, 'editor');
    $teamChannel = privateChannel($editorTeam, 'pali team');
    $readerTeam = makeGroup(makeStudio('t2'));
    addGroupMember($readerTeam, $me);
    privateChannel($readerTeam, 'pali read-only team');
    $shared = privateChannel(makeStudio('a'), 'pali shared 20');
    grantChannelShare($shared, $me, 20);
    $readOnly = privateChannel(makeStudio('b'), 'pali shared 10');
    grantChannelShare($readOnly, $me, 10);
    makeChannel(makeStudio('c'), 'pali public');

    expect(channelIds($this->getJson('/api/v3/channels/editable?search=PALI', authHeader($me))->assertOk()->json('data')))
        ->toEqualCanonicalizing([$mine, $teamChannel, $shared]);
    expect(channelIds($this->getJson('/api/v3/channels/editable?search=team', authHeader($me))->json('data')))
        ->toBe([$teamChannel]);
    $this->getJson('/api/v3/channels/editable')->assertUnauthorized();
});

/* ---------------- 公开不列出（status 20） ---------------- */

it('lets anyone open an unlisted channel but keeps it out of public lists', function () {
    $owner = makeStudio('owner');
    $unlisted = makeChannel($owner, 'unlisted channel');
    Channel::where('uid', $unlisted)->update(['status' => Channel::STATUS_UNLISTED]);
    makeSentence($unlisted, 8, 8, 1, 'text');
    (new ProgressChapter)->forceFill([
        'uid' => (string) Str::uuid(), 'book' => 8, 'para' => 8, 'channel_id' => $unlisted,
        'lang' => 'zh', 'all_trans' => 100, 'public' => 100, 'progress' => 1.0,
    ])->save();
    $stranger = authHeader(makeStudio('stranger'));

    // 拿到链接能看（不登录也行），但不能改
    $this->getJson("/api/v3/channels/{$unlisted}")
        ->assertOk()
        ->assertJsonPath('data.abilities', ['read' => true, 'write' => false, 'update' => false, 'delete' => false]);

    // 面向他人的列表一律不出现
    expect(channelIds($this->getJson('/api/v3/channels', $stranger)->json('data')))->not->toContain($unlisted);
    expect(channelIds($this->getJson('/api/v3/tipitaka-channels?lang=zh')->json('data')))->not->toContain($unlisted);
    expect(channelIds($this->getJson('/api/v3/tipitaka-paragraph-channels/8/8', $stranger)->json('data')))->not->toContain($unlisted);
    expect(array_column(array_column($this->getJson('/api/v3/tipitaka-chapter-channels/8/8', $stranger)->json('data'), 'channel'), 'id'))
        ->not->toContain($unlisted);

    // 拥有者自己的列表里照常
    expect(channelIds($this->getJson("/api/v3/studios/{$owner}/channels", authHeader($owner))->json('data')))->toContain($unlisted);
    expect(channelIds($this->getJson('/api/v3/tipitaka-paragraph-channels/8/8', authHeader($owner))->json('data')))->toContain($unlisted);
});

it('accepts unlisted as a status and rejects unknown ones', function () {
    $me = makeStudio('me');
    $channel = makeChannel($me);

    $this->patchJson("/api/v3/channels/{$channel}", ['status' => Channel::STATUS_UNLISTED], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.status', 20);
    $this->patchJson("/api/v3/channels/{$channel}", ['status' => 15], authHeader($me))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
    $this->postJson("/api/v3/studios/{$me}/channels", ['name' => 'u', 'type' => 'translation', 'lang' => 'en', 'status' => 20], authHeader($me))
        ->assertCreated()
        ->assertJsonPath('data.status', 20);
});
