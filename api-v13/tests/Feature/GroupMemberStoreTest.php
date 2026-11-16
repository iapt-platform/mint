<?php

use App\Models\GroupMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * POST /v2/group-member（往团队里加人）的快照测试。
 *
 * 原实现不校验登录也不校验调用者在团队里的身份，任何人都能把自己加进任意团队，
 * 从而拿到共享给该团队的资源权限（库里有两百多条 editor 级的团队共享）。
 * 修复只收紧「谁能调」：与同控制器 destroy 同口径，团队的 owner / manager 才能加人；
 * 合法请求的响应形状不变——v4 的 AddMember 还在调它。
 */
it('lets the team owner add a member', function () {
    $owner = makeStudio('owner');
    $team = makeGroup($owner);
    $friend = makeStudio('friend');

    $this->postJson('/api/v2/group-member', ['user_id' => $friend, 'group_id' => $team], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('data.user_id', $friend)
        ->assertJsonPath('data.group_id', $team)
        ->assertJsonPath('data.power', 2)
        ->assertJsonPath('data.role', 'member');

    expect(GroupMember::where('group_id', $team)->where('user_id', $friend)->value('power'))->toBe(2);
});

it('lets a team manager add a member', function () {
    $team = makeGroup(makeStudio('owner'));
    $manager = makeStudio('manager');
    addGroupMember($team, $manager, 1);
    $friend = makeStudio('friend');

    $this->postJson('/api/v2/group-member', ['user_id' => $friend, 'group_id' => $team], authHeader($manager))
        ->assertJsonPath('ok', true);

    expect(GroupMember::where('group_id', $team)->where('user_id', $friend)->exists())->toBeTrue();
});

it('still reports an existing member', function () {
    $owner = makeStudio('owner');
    $team = makeGroup($owner);
    $friend = makeStudio('friend');
    addGroupMember($team, $friend);

    $this->postJson('/api/v2/group-member', ['user_id' => $friend, 'group_id' => $team], authHeader($owner))
        ->assertJsonPath('ok', false)
        ->assertJsonPath('message', 'member exists');
});

it('rejects anonymous requests', function () {
    $team = makeGroup(makeStudio('owner'));
    $intruder = makeStudio('intruder');

    $this->postJson('/api/v2/group-member', ['user_id' => $intruder, 'group_id' => $team])
        ->assertStatus(401)
        ->assertJsonPath('ok', false);

    expect(GroupMember::where('group_id', $team)->where('user_id', $intruder)->exists())->toBeFalse();
});

it('does not let outsiders add themselves', function () {
    $team = makeGroup(makeStudio('owner'));
    $intruder = makeStudio('intruder');

    $this->postJson('/api/v2/group-member', ['user_id' => $intruder, 'group_id' => $team], authHeader($intruder))
        ->assertStatus(403)
        ->assertJsonPath('ok', false);

    expect(GroupMember::where('group_id', $team)->where('user_id', $intruder)->exists())->toBeFalse();
});

it('does not let plain members add people', function () {
    $team = makeGroup(makeStudio('owner'));
    $member = makeStudio('member');
    addGroupMember($team, $member);
    $outsider = makeStudio('outsider');

    $this->postJson('/api/v2/group-member', ['user_id' => $outsider, 'group_id' => $team], authHeader($member))
        ->assertStatus(403);

    expect(GroupMember::where('group_id', $team)->where('user_id', $outsider)->exists())->toBeFalse();
});
