<?php

use App\Models\UserInfo;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * v3 登录与「我是谁」。这里断言的是形状而非契约（spectator 未安装）。
 *
 * 端点：
 *   POST /api/v3/sessions   用户名或邮箱 + 密码 → token + 用户
 *   GET  /api/v3/me         当前用户（bearer）
 */

/**
 * 建一个密码为 $password 的账号，返回 userid。
 */
function makeAccount(string $username, string $password = 'secret-pw', ?string $role = null): string
{
    $uid = makeStudio($username);
    UserInfo::where('userid', $uid)->update(['password' => md5($password), 'role' => $role]);

    return $uid;
}

it('signs in with a username and returns token and user in one response', function () {
    $uid = makeAccount('alice_1', 'secret-pw', json_encode(['basic']));

    $data = $this->postJson('/api/v3/sessions', ['login' => 'alice_1', 'password' => 'secret-pw'])
        ->assertCreated()
        ->json('data');

    expect($data)->toHaveKeys(['token', 'user'])
        ->and($data['user'])->toHaveKeys(['id', 'nickName', 'userName', 'realName', 'avatar', 'roles', 'email'])
        ->and($data['user']['id'])->toBe($uid)
        ->and($data['user']['roles'])->toBe(['basic'])
        ->and($data['user'])->not->toHaveKey('password');

    // token 能直接用来访问 v3 与 v2（两边互认）
    $this->getJson('/api/v3/me', ['Authorization' => 'Bearer '.$data['token']])
        ->assertOk()
        ->assertJsonPath('data.id', $uid);
    $this->getJson('/api/v2/auth/current', ['Authorization' => 'Bearer '.$data['token']])
        ->assertJsonPath('ok', true);
});

it('signs in with an email, case-insensitively', function () {
    makeAccount('bob_1');

    $this->postJson('/api/v3/sessions', ['login' => 'BOB_1@Example.Test', 'password' => 'secret-pw'])
        ->assertCreated()
        ->assertJsonPath('data.user.userName', 'bob_1');
});

it('does not trim the password', function () {
    makeAccount('carol_1', ' spaced ');

    $this->postJson('/api/v3/sessions', ['login' => 'carol_1', 'password' => ' spaced '])->assertCreated();
    $this->postJson('/api/v3/sessions', ['login' => 'carol_1', 'password' => 'spaced'])->assertStatus(422);
});

it('rejects a wrong password and an unknown account the same way', function () {
    makeAccount('dave_1');

    $wrong = $this->postJson('/api/v3/sessions', ['login' => 'dave_1', 'password' => 'nope'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['login']])
        ->json('errors.login.0');
    $unknown = $this->postJson('/api/v3/sessions', ['login' => 'nobody_1', 'password' => 'nope'])
        ->assertStatus(422)
        ->json('errors.login.0');

    expect($wrong)->toBe($unknown);
});

it('requires login and password', function () {
    $this->postJson('/api/v3/sessions', [])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['login', 'password']]);
});

it('throttles guessing on one account', function () {
    makeAccount('erin_1');

    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v3/sessions', ['login' => 'erin_1', 'password' => 'nope'])->assertStatus(422);
    }

    $this->postJson('/api/v3/sessions', ['login' => 'erin_1', 'password' => 'secret-pw'])->assertStatus(429);
});

it('reports the root admin as root', function () {
    $uid = makeAccount('root_1');
    config(['mint.admin.root_uuid' => $uid]);

    $this->postJson('/api/v3/sessions', ['login' => 'root_1', 'password' => 'secret-pw'])
        ->assertCreated()
        ->assertJsonPath('data.user.roles', ['root']);
});

it('returns an empty roles array when the account has none', function () {
    $uid = makeAccount('frank_1');

    $this->getJson('/api/v3/me', authHeader($uid))
        ->assertOk()
        ->assertJsonPath('data.roles', [])
        ->assertJsonPath('data.avatar', null)
        ->assertJsonMissingPath('data.token');
});

it('rejects /me without a valid token', function () {
    $this->getJson('/api/v3/me')->assertUnauthorized();
    $this->getJson('/api/v3/me', ['Authorization' => 'Bearer garbage'])->assertUnauthorized();
});

it('rejects /me when the account no longer exists', function () {
    $uid = makeAccount('gone_1');
    $token = AuthService::getUserToken($uid);
    UserInfo::where('userid', $uid)->delete();

    $this->getJson('/api/v3/me', ['Authorization' => 'Bearer '.$token])->assertUnauthorized();
});
