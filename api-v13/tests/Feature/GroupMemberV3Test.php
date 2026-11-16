<?php

use App\Models\GroupMember;
use App\Services\V3\GroupAbilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 团队成员（团队空间的身份与角色）。
 *
 * 端点：
 *   GET    /api/v3/me/groups                         我加入的团队，带我的角色
 *   GET    /api/v3/groups/{group}/members            成员列表（成员可看）
 *   POST   /api/v3/groups/{group}/members            加人（owner / manager）
 *   PATCH  /api/v3/groups/{group}/members/{member}   改角色（owner / manager，owner 行不可改）
 *   DELETE /api/v3/groups/{group}/members/{member}   移人 / 本人退出（owner 不可）
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

/* ---------------- 角色推导 ---------------- */

it('derives roles from v2 power when role is empty', function (int $power, string $role) {
    $member = (new GroupMember)->forceFill(['power' => $power]);

    expect($member->effectiveRole())->toBe($role);
})->with([
    'owner' => [0, 'owner'],
    'manager' => [1, 'manager'],
    'member' => [2, 'reader'],
]);

it('prefers the role column over power', function () {
    $member = (new GroupMember)->forceFill(['power' => 2, 'role' => 'editor']);

    expect($member->effectiveRole())->toBe('editor');
});

it('maps roles to abilities', function (string $role, array $allowed) {
    foreach (['read', 'create', 'update', 'delete', 'manage_members', 'manage_group', 'delete_group'] as $ability) {
        expect(GroupAbilityService::roleAllows($role, $ability))
            ->toBe(in_array($ability, $allowed, true), "{$role} / {$ability}");
    }
})->with([
    'owner' => ['owner', ['read', 'create', 'update', 'delete', 'manage_members', 'manage_group', 'delete_group']],
    'manager' => ['manager', ['read', 'create', 'update', 'manage_members', 'manage_group']],
    'editor' => ['editor', ['read', 'update']],
    'reader' => ['reader', ['read']],
]);

it('treats the owner uid itself as full ability and outsiders as none', function () {
    [$team, $users] = makeTeam();
    $abilities = app(GroupAbilityService::class);

    expect($abilities->can($users['owner'], $users['owner'], 'delete'))->toBeTrue();
    expect($abilities->can($users['editor'], $team, 'update'))->toBeTrue();
    expect($abilities->can($users['editor'], $team, 'create'))->toBeFalse();
    expect($abilities->can($users['outsider'], $team, 'read'))->toBeFalse();
});

/* ---------------- GET /me/groups ---------------- */

it('lists my teams with my role', function () {
    [$team, $users] = makeTeam();
    makeGroup($users['outsider'], 'not mine');

    $data = $this->getJson('/api/v3/me/groups', authHeader($users['editor']))
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(1);
    expect($data[0])->toMatchArray(['id' => $team, 'name' => 'test team', 'role' => 'editor']);
    expect($data[0])->toHaveKeys(['description', 'owner_id', 'joined_at']);
    expect($data[0]['member_id'])->toBe(memberId($team, $users['editor']));
});

it('requires login for my teams', function () {
    $this->getJson('/api/v3/me/groups')->assertUnauthorized();
});

/* ---------------- GET members ---------------- */

it('lets any member list the members', function () {
    [$team, $users] = makeTeam();

    $data = $this->getJson("/api/v3/groups/{$team}/members", authHeader($users['reader']))
        ->assertOk()
        ->json('data');

    $roles = array_column($data, 'role');
    expect(array_slice($roles, 0, 2))->toBe(['owner', 'manager']);
    expect(array_slice($roles, 2))->toEqualCanonicalizing(['editor', 'reader']);
    expect($data[0])->toHaveKeys(['id', 'group_id', 'role', 'joined_at', 'user']);
    expect($data[0]['id'])->toBeString();
    expect($data[0]['user']['id'])->toBe($users['owner']);
});

it('hides the member list from outsiders', function () {
    [$team, $users] = makeTeam();

    $this->getJson("/api/v3/groups/{$team}/members", authHeader($users['outsider']))
        ->assertForbidden();
});

it('answers 401 before looking the team up', function () {
    // 认证要排在路由模型绑定之前：未登录不该靠 404 / 200 探出团队是否存在
    $this->getJson('/api/v3/groups/'.Str::uuid().'/members')->assertUnauthorized();
});

it('returns 404 for an unknown team', function () {
    $this->getJson('/api/v3/groups/'.Str::uuid().'/members', authHeader(makeStudio('someone')))
        ->assertNotFound();
});

it('does not query per member', function () {
    $countQueriesFor = function (int $extra): int {
        [$team, $users] = makeTeam();
        for ($i = 0; $i < $extra; $i++) {
            addGroupMember($team, makeStudio('extra-'.Str::random(8)));
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/groups/{$team}/members", authHeader($users['reader']))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(10))->toBe($countQueriesFor(0));
});

/* ---------------- POST members ---------------- */

it('lets owners and managers add members, defaulting to reader', function (string $actor) {
    [$team, $users] = makeTeam();
    $newcomer = makeStudio('newcomer-'.Str::random(6));

    $this->postJson("/api/v3/groups/{$team}/members", ['user_id' => $newcomer], authHeader($users[$actor]))
        ->assertCreated()
        ->assertJsonPath('data.role', 'reader')
        ->assertJsonPath('data.user.id', $newcomer);

    $row = GroupMember::where('group_id', $team)->where('user_id', $newcomer)->first();
    expect($row->role)->toBe('reader');
    expect((int) $row->power)->toBe(2);
})->with(['owner', 'manager']);

it('adds a member with a given role and keeps power in sync', function () {
    [$team, $users] = makeTeam();
    $newcomer = makeStudio('newcomer');

    $this->postJson("/api/v3/groups/{$team}/members", ['user_id' => $newcomer, 'role' => 'manager'], authHeader($users['owner']))
        ->assertCreated()
        ->assertJsonPath('data.role', 'manager');

    expect((int) GroupMember::where('user_id', $newcomer)->value('power'))->toBe(1);
});

it('does not let editors, readers or outsiders add members', function (string $actor) {
    [$team, $users] = makeTeam();
    $newcomer = makeStudio('newcomer');

    $this->postJson("/api/v3/groups/{$team}/members", ['user_id' => $newcomer], authHeader($users[$actor]))
        ->assertForbidden();

    expect(GroupMember::where('user_id', $newcomer)->exists())->toBeFalse();
})->with(['editor', 'reader', 'outsider']);

it('rejects invalid additions', function (array $payload, string $field) {
    [$team, $users] = makeTeam();
    $payload = array_map(fn ($value) => $value === ':editor' ? $users['editor'] : $value, $payload);

    $this->postJson("/api/v3/groups/{$team}/members", $payload, authHeader($users['owner']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'missing user' => [[], 'user_id'],
    'not a uuid' => [['user_id' => 'abc'], 'user_id'],
    'unknown user' => [['user_id' => '00000000-0000-4000-8000-000000000000'], 'user_id'],
    'already a member' => [['user_id' => ':editor'], 'user_id'],
    'owner role' => [['user_id' => '00000000-0000-4000-8000-000000000000', 'role' => 'owner'], 'role'],
]);

it('requires login to add members', function () {
    [$team] = makeTeam();

    $this->postJson("/api/v3/groups/{$team}/members", ['user_id' => (string) Str::uuid()])
        ->assertUnauthorized();
});

/* ---------------- PATCH members ---------------- */

it('lets managers change roles, including to manager', function () {
    [$team, $users] = makeTeam();
    $reader = memberId($team, $users['reader']);

    $this->patchJson("/api/v3/groups/{$team}/members/{$reader}", ['role' => 'manager'], authHeader($users['manager']))
        ->assertOk()
        ->assertJsonPath('data.role', 'manager');

    $row = GroupMember::find($reader);
    expect($row->role)->toBe('manager');
    expect((int) $row->power)->toBe(1);
});

it('demotes a v2 manager to editor with power kept at member level', function () {
    [$team, $users] = makeTeam();
    $manager = memberId($team, $users['manager']);

    $this->patchJson("/api/v3/groups/{$team}/members/{$manager}", ['role' => 'editor'], authHeader($users['owner']))
        ->assertOk()
        ->assertJsonPath('data.role', 'editor');

    expect((int) GroupMember::find($manager)->power)->toBe(2);
});

it('never changes the owner row', function () {
    [$team, $users] = makeTeam();
    $owner = memberId($team, $users['owner']);

    $this->patchJson("/api/v3/groups/{$team}/members/{$owner}", ['role' => 'reader'], authHeader($users['manager']))
        ->assertForbidden();

    expect(GroupMember::find($owner)->effectiveRole())->toBe('owner');
});

it('does not let editors change roles', function () {
    [$team, $users] = makeTeam();
    $reader = memberId($team, $users['reader']);

    $this->patchJson("/api/v3/groups/{$team}/members/{$reader}", ['role' => 'editor'], authHeader($users['editor']))
        ->assertForbidden();
});

it('rejects assigning the owner role', function () {
    [$team, $users] = makeTeam();
    $reader = memberId($team, $users['reader']);

    $this->patchJson("/api/v3/groups/{$team}/members/{$reader}", ['role' => 'owner'], authHeader($users['owner']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');
});

it('returns 404 for a member of another team', function () {
    [$team, $users] = makeTeam();
    [$otherTeam, $otherUsers] = makeTeam();
    $stranger = memberId($otherTeam, $otherUsers['reader']);

    $this->patchJson("/api/v3/groups/{$team}/members/{$stranger}", ['role' => 'editor'], authHeader($users['owner']))
        ->assertNotFound();
});

it('does not register PUT', function () {
    [$team, $users] = makeTeam();
    $reader = memberId($team, $users['reader']);

    $this->putJson("/api/v3/groups/{$team}/members/{$reader}", ['role' => 'editor'], authHeader($users['owner']))
        ->assertMethodNotAllowed();
});

/* ---------------- DELETE members ---------------- */

it('lets managers remove members', function () {
    [$team, $users] = makeTeam();
    $editor = GroupMember::where('group_id', $team)->where('user_id', $users['editor'])->first();

    $this->deleteJson("/api/v3/groups/{$team}/members/{$editor->id}", [], authHeader($users['manager']))
        ->assertNoContent();

    $this->assertModelMissing($editor);
});

it('lets a reader leave the team', function () {
    [$team, $users] = makeTeam();
    $reader = GroupMember::where('group_id', $team)->where('user_id', $users['reader'])->first();

    $this->deleteJson("/api/v3/groups/{$team}/members/{$reader->id}", [], authHeader($users['reader']))
        ->assertNoContent();

    $this->assertModelMissing($reader);
});

it('does not let a reader remove someone else', function () {
    [$team, $users] = makeTeam();
    $editor = memberId($team, $users['editor']);

    $this->deleteJson("/api/v3/groups/{$team}/members/{$editor}", [], authHeader($users['reader']))
        ->assertForbidden();
});

it('never removes the owner, not even by themselves', function (string $actor) {
    [$team, $users] = makeTeam();
    $owner = memberId($team, $users['owner']);

    $this->deleteJson("/api/v3/groups/{$team}/members/{$owner}", [], authHeader($users[$actor]))
        ->assertForbidden();

    expect(GroupMember::find($owner))->not->toBeNull();
})->with(['owner', 'manager']);
