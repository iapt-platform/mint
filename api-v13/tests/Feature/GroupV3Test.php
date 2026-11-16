<?php

use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Models\Share;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 团队本身。
 *
 * 端点：
 *   GET    /api/v3/groups?search=        搜公开团队（登录）
 *   POST   /api/v3/groups                建团队（登录，建的人成为 owner）
 *   GET    /api/v3/groups/{group}        详情（公开团队可不登录；私有团队只有成员）
 *   PATCH  /api/v3/groups/{group}        改信息（owner / manager）
 *   DELETE /api/v3/groups/{group}        删团队（owner；名下有资源 409）
 *   GET    /api/v3/me/groups?owned=&search=   我创建的 / 我参加的
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function makePrivateTeam(): array
{
    [$team, $users] = makeTeam();
    GroupInfo::where('uid', $team)->update(['status' => GroupInfo::STATUS_PRIVATE]);

    return [$team, $users];
}

/* ---------------- 搜公开团队 ---------------- */

it('searches public teams only', function () {
    $me = makeStudio('me');
    $public = makeGroup(makeStudio('a'), 'pali readers');
    $private = makeGroup(makeStudio('b'), 'pali secret');
    GroupInfo::where('uid', $private)->update(['status' => GroupInfo::STATUS_PRIVATE]);
    makeGroup(makeStudio('c'), 'other');

    $data = $this->getJson('/api/v3/groups?search=PALI', authHeader($me))
        ->assertOk()
        ->json('data');

    expect(array_column($data, 'id'))->toBe([$public]);
    expect($data[0])->toHaveKeys(['id', 'name', 'description', 'status', 'owner', 'created_at', 'updated_at']);
    expect($data[0])->not->toHaveKey('role');
});

it('requires login to search teams', function () {
    $this->getJson('/api/v3/groups')->assertUnauthorized();
});

it('does not query per team when searching', function () {
    $me = makeStudio('me');
    $countQueriesFor = function (int $teams) use ($me): int {
        for ($i = 0; $i < $teams; $i++) {
            makeGroup(makeStudio('owner-'.Str::random(8)), 'n+1 team '.Str::random(4));
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v3/groups?search=n%2B1', authHeader($me))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(8))->toBe($countQueriesFor(1));
});

/* ---------------- 建团队 ---------------- */

it('creates a team with the creator as owner', function () {
    $me = makeStudio('me');

    $response = $this->postJson('/api/v3/groups', ['name' => '新团队', 'description' => '简介'], authHeader($me))
        ->assertCreated()
        ->assertJsonPath('data.name', '新团队')
        ->assertJsonPath('data.status', GroupInfo::STATUS_PUBLIC)
        ->assertJsonPath('data.role', 'owner')
        ->assertJsonPath('data.owner.id', $me);

    $team = $response->json('data.id');
    $member = GroupMember::where('group_id', $team)->where('user_id', $me)->first();
    expect($member->role)->toBe('owner');
    expect((int) $member->power)->toBe(0);
});

it('rejects a duplicate name under the same owner', function () {
    $me = makeStudio('me');
    makeGroup($me, 'dup');

    $this->postJson('/api/v3/groups', ['name' => 'dup'], authHeader($me))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    // 别人名下同名不冲突
    $this->postJson('/api/v3/groups', ['name' => 'dup'], authHeader(makeStudio('other')))
        ->assertCreated();
});

it('validates new teams', function (array $payload, string $field) {
    $this->postJson('/api/v3/groups', $payload, authHeader(makeStudio('me')))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'missing name' => [[], 'name'],
    'name too long' => [['name' => str_repeat('a', 65)], 'name'],
    'bad status' => [['name' => 'x', 'status' => 20], 'status'],
]);

it('requires login to create a team', function () {
    $this->postJson('/api/v3/groups', ['name' => 'x'])->assertUnauthorized();
});

/* ---------------- 详情 ---------------- */

it('shows a public team to anyone, with the viewer role', function () {
    [$team, $users] = makeTeam();

    $this->getJson("/api/v3/groups/{$team}")
        ->assertOk()
        ->assertJsonPath('data.id', $team)
        ->assertJsonPath('data.role', null);

    $this->getJson("/api/v3/groups/{$team}", authHeader($users['editor']))
        ->assertJsonPath('data.role', 'editor');
});

it('hides a private team from outsiders', function () {
    [$team, $users] = makePrivateTeam();

    $this->getJson("/api/v3/groups/{$team}")->assertForbidden();
    $this->getJson("/api/v3/groups/{$team}", authHeader($users['outsider']))->assertForbidden();
    $this->getJson("/api/v3/groups/{$team}", authHeader($users['reader']))
        ->assertOk()
        ->assertJsonPath('data.role', 'reader');
});

it('returns 404 for an unknown team', function () {
    $this->getJson('/api/v3/groups/'.Str::uuid())->assertNotFound();
});

/* ---------------- 改团队 ---------------- */

it('lets owners and managers update the team', function (string $actor) {
    [$team, $users] = makeTeam();

    $this->patchJson("/api/v3/groups/{$team}", ['name' => 'renamed', 'status' => 10], authHeader($users[$actor]))
        ->assertOk()
        ->assertJsonPath('data.name', 'renamed')
        ->assertJsonPath('data.status', 10)
        ->assertJsonPath('data.role', $actor);

    // 成员记录上冗余的团队名跟着改
    expect(GroupMember::where('group_id', $team)->pluck('group_name')->unique()->all())->toBe(['renamed']);
})->with(['owner', 'manager']);

it('only changes the fields sent', function () {
    [$team, $users] = makeTeam();
    GroupInfo::where('uid', $team)->update(['description' => 'keep me']);

    $this->patchJson("/api/v3/groups/{$team}", ['name' => 'renamed'], authHeader($users['owner']))
        ->assertOk()
        ->assertJsonPath('data.description', 'keep me');
});

it('does not let editors, readers or outsiders update the team', function (string $actor) {
    [$team, $users] = makeTeam();

    $this->patchJson("/api/v3/groups/{$team}", ['name' => 'hacked'], authHeader($users[$actor]))
        ->assertForbidden();

    expect(GroupInfo::find($team)->name)->toBe('test team');
})->with(['editor', 'reader', 'outsider']);

it('rejects renaming to a name the owner already uses', function () {
    [$team, $users] = makeTeam();
    makeGroup($users['owner'], 'taken');

    $this->patchJson("/api/v3/groups/{$team}", ['name' => 'taken'], authHeader($users['manager']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('does not register PUT on teams', function () {
    [$team, $users] = makeTeam();

    $this->putJson("/api/v3/groups/{$team}", ['name' => 'x'], authHeader($users['owner']))
        ->assertMethodNotAllowed();
});

/* ---------------- 删团队 ---------------- */

it('lets the owner delete a team with its members and shares', function () {
    [$team, $users] = makeTeam();
    $share = (new Share)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'res_id' => (string) Str::uuid(),
        'res_type' => 2,
        'cooperator_id' => $team,
        'cooperator_type' => 1,
        'power' => 20,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ]);
    $share->save();

    $this->deleteJson("/api/v3/groups/{$team}", [], authHeader($users['owner']))
        ->assertNoContent();

    expect(GroupInfo::find($team))->toBeNull();
    expect(GroupMember::where('group_id', $team)->exists())->toBeFalse();
    $this->assertModelMissing($share);
});

it('refuses to delete a team that still owns resources', function () {
    [$team, $users] = makeTeam();
    makeChannel($team, 'team channel');

    $this->deleteJson("/api/v3/groups/{$team}", [], authHeader($users['owner']))
        ->assertConflict()
        ->assertJsonPath('resources.channel', 1);

    expect(GroupInfo::find($team))->not->toBeNull();
});

it('only lets the owner delete a team', function (string $actor) {
    [$team, $users] = makeTeam();

    $this->deleteJson("/api/v3/groups/{$team}", [], authHeader($users[$actor]))
        ->assertForbidden();

    expect(GroupInfo::find($team))->not->toBeNull();
})->with(['manager', 'editor', 'outsider']);

/* ---------------- 我的团队：owned / search ---------------- */

it('filters my teams by ownership and name', function () {
    $me = makeStudio('me');
    $mine = makeGroup($me, 'mine alpha');
    $joined = makeGroup(makeStudio('other'), 'joined beta');
    addGroupMember($joined, $me);

    $ids = fn (string $query) => array_column(
        $this->getJson("/api/v3/me/groups{$query}", authHeader($me))->assertOk()->json('data'),
        'id'
    );

    expect($ids(''))->toEqualCanonicalizing([$mine, $joined]);
    expect($ids('?owned=true'))->toBe([$mine]);
    expect($ids('?owned=false'))->toBe([$joined]);
    expect($ids('?search=BETA'))->toBe([$joined]);

    $row = $this->getJson('/api/v3/me/groups?owned=false', authHeader($me))->json('data.0');
    expect($row['owner']['id'])->not->toBe($me);
    expect($row)->toHaveKeys(['status', 'created_at', 'joined_at']);

    $this->getJson('/api/v3/me/groups?owned=true', authHeader($me))->assertJsonPath('meta.total', 1);
});

it('rejects a bad owned filter', function () {
    $this->getJson('/api/v3/me/groups?owned=maybe', authHeader(makeStudio('me')))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('owned');
});
