<?php

use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Services\V3\GroupInviteService;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * v3 团队邀请链接。
 *
 * 端点：
 *   POST /api/v3/groups/{group}/invite-links   签一张链接（owner / manager）
 *   GET  /api/v3/group-invites/{token}         打开链接（公开）
 *   POST /api/v3/me/groups {invite}            同意，以 reader 加入
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function issueInvite(string $team, string $inviter): string
{
    return test()->postJson("/api/v3/groups/{$team}/invite-links", [], authHeader($inviter))
        ->assertCreated()
        ->json('data.token');
}

/* ---------------- 签发 ---------------- */

it('lets owners and managers issue an invite link', function (string $role) {
    [$team, $users] = makeTeam();

    $data = $this->postJson("/api/v3/groups/{$team}/invite-links", [], authHeader($users[$role]))
        ->assertCreated()
        ->json('data');

    expect($data)->toHaveKeys(['token', 'role', 'expires_at']);
    expect($data['role'])->toBe('reader');
    expect($data['expires_at'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/');
})->with(['owner', 'manager']);

it('forbids editors, readers and outsiders from issuing links', function (string $role) {
    [$team, $users] = makeTeam();

    $this->postJson("/api/v3/groups/{$team}/invite-links", [], authHeader($users[$role]))
        ->assertForbidden();
})->with(['editor', 'reader', 'outsider']);

it('requires login to issue a link', function () {
    [$team] = makeTeam();

    $this->postJson("/api/v3/groups/{$team}/invite-links")->assertUnauthorized();
});

it('forbids ai models from issuing links', function () {
    [$team, $users] = makeTeam();
    $model = makeModelAccount($users['owner']);
    addGroupMember($team, $model, 1);

    $this->postJson("/api/v3/groups/{$team}/invite-links", [], authHeader($model))
        ->assertForbidden();
});

/* ---------------- 打开链接 ---------------- */

it('shows the invitation without login', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['manager']);

    $data = $this->getJson("/api/v3/group-invites/{$token}")->assertOk()->json('data');

    expect($data['group'])->toMatchArray(['id' => $team, 'name' => 'test team']);
    expect($data['group'])->toHaveKeys(['description', 'status', 'owner']);
    expect($data['inviter']['id'])->toBe($users['manager']);
    expect($data['role'])->toBe('reader');
    expect($data['member_role'])->toBeNull();
});

it('tells a logged-in member their current role', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['owner']);

    $this->getJson("/api/v3/group-invites/{$token}", authHeader($users['editor']))
        ->assertOk()
        ->assertJsonPath('data.member_role', 'editor');
    $this->getJson("/api/v3/group-invites/{$token}", authHeader($users['outsider']))
        ->assertOk()
        ->assertJsonPath('data.member_role', null);
});

it('shows invitations to private teams too', function () {
    [$team, $users] = makeTeam();
    GroupInfo::where('uid', $team)->update(['status' => GroupInfo::STATUS_PRIVATE]);
    $token = issueInvite($team, $users['owner']);

    $this->getJson("/api/v3/group-invites/{$token}")->assertOk();
});

it('rejects forged, expired and foreign tokens', function (Closure $makeToken) {
    [$team, $users] = makeTeam();
    $token = $makeToken($team, $users['owner']);

    $detail = $this->getJson("/api/v3/group-invites/{$token}")
        ->assertNotFound()
        ->json('detail');
    expect($detail)->not->toBe('messages.group_invite_invalid');
})->with([
    'wrong key' => [fn (string $team, string $owner) => JWT::encode(
        ['typ' => 'group-invite', 'grp' => $team, 'iss' => $owner, 'exp' => time() + 60], hash('sha512', 'not-the-key'), 'HS512')],
    'expired' => [fn (string $team, string $owner) => JWT::encode(
        ['typ' => 'group-invite', 'grp' => $team, 'iss' => $owner, 'nbf' => time() - 120, 'exp' => time() - 60],
        hash('sha512', config('mint.app.jwt_secrets_key').'|group-invite'), 'HS512')],
    'agent grant key' => [fn (string $team, string $owner) => JWT::encode(
        ['typ' => 'group-invite', 'grp' => $team, 'iss' => $owner, 'exp' => time() + 60],
        hash('sha512', config('mint.app.jwt_secrets_key').'|agent-grant'), 'HS512')],
]);

it('invalidates links once the inviter loses the manager role', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['manager']);

    GroupMember::where('group_id', $team)->where('user_id', $users['manager'])
        ->update(['role' => 'reader', 'power' => 2]);

    $this->getJson("/api/v3/group-invites/{$token}")->assertNotFound();
    $this->postJson('/api/v3/me/groups', ['invite' => $token], authHeader($users['outsider']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('invite');
});

it('invalidates links when the team is deleted', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['owner']);
    GroupInfo::where('uid', $team)->delete();

    $this->getJson("/api/v3/group-invites/{$token}")->assertNotFound();
});

/* ---------------- 同意加入 ---------------- */

it('joins the team as a reader', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['manager']);

    $data = $this->postJson('/api/v3/me/groups', ['invite' => $token], authHeader($users['outsider']))
        ->assertCreated()
        ->json('data');

    expect($data)->toMatchArray(['id' => $team, 'role' => GroupInviteService::ROLE]);
    expect($data)->toHaveKeys(['member_id', 'joined_at']);
    $row = GroupMember::where('group_id', $team)->where('user_id', $users['outsider'])->first();
    expect($row->role)->toBe('reader');
    expect((int) $row->power)->toBe(2);
});

it('keeps the existing role when a member accepts again', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['owner']);

    $this->postJson('/api/v3/me/groups', ['invite' => $token], authHeader($users['editor']))
        ->assertOk()
        ->assertJsonPath('data.role', 'editor');
    expect(GroupMember::where('group_id', $team)->where('user_id', $users['editor'])->count())->toBe(1);
});

it('requires login to accept', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['owner']);

    $this->postJson('/api/v3/me/groups', ['invite' => $token])->assertUnauthorized();
});

it('requires an invite token to accept', function () {
    $this->postJson('/api/v3/me/groups', [], authHeader(makeStudio('someone')))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('invite');
});

it('rejects a garbage invite token', function () {
    $this->postJson('/api/v3/me/groups', ['invite' => 'a.b.c'], authHeader(makeStudio('someone')))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('invite');
});

it('forbids ai models from accepting', function () {
    [$team, $users] = makeTeam();
    $token = issueInvite($team, $users['owner']);
    $model = makeModelAccount($users['outsider']);

    $this->postJson('/api/v3/me/groups', ['invite' => $token], authHeader($model))
        ->assertForbidden();
    expect(GroupMember::where('group_id', $team)->where('user_id', $model)->exists())->toBeFalse();
});
