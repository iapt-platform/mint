<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v1 时代的 cookie 认证绕过已从 v2 控制器里清干净。
 *
 * 这几条是 characterization test：改动的是冻结中的 v2，必须钉住「未登录时明确
 * 被拒」和「带 bearer 时照常工作」两件事，而不是靠 cookie 悄悄放行。
 *
 * 带 cookie 的用例必须直接操作 `$_COOKIE` 超全局——`withUnencryptedCookies()`
 * 只填 Symfony Request 的 cookie 袋子，不碰超全局，用它写出来的测试是空转的。
 */
function withV1Cookie(array $cookies, callable $fn): mixed
{
    $backup = $_COOKIE;
    $_COOKIE = $cookies;
    try {
        return $fn();
    } finally {
        $_COOKIE = $backup;
    }
}

it('removed the dead DELETE /v2/like route', function () {
    // 前端只用 POST /v2/like 与 DELETE /v2/like/{id}；这条无 id 的 DELETE 没人调，
    // 而且 cookie 是它唯一的认证方式，已整条删除
    $this->deleteJson('/api/v2/like')->assertStatus(405);
});

it('keeps the like routes the frontends actually use', function () {
    $target = (string) Str::uuid();

    $this->getJson("/api/v2/like?view=count&target_id={$target}")
        ->assertOk()
        ->assertJsonPath('ok', true);
});

it('rejects sentence fulltext without a bearer token', function () {
    // 这个 view 以前靠 cookie，且未登录时 $userUid 是未定义变量
    // $this->error() 默认 400，这是 v2 既有的失败口径，不是这次改出来的
    $json = withV1Cookie(['user_uid' => (string) Str::uuid()], fn () => $this
        ->getJson('/api/v2/sentence?view=fulltext&key=abc')
        ->assertStatus(400)
        ->json());

    expect($json['ok'])->toBeFalse()
        ->and($json['message'])->toBe(__('auth.failed'));
});

it('serves sentence fulltext to a bearer-authenticated user', function () {
    $uid = makeStudio('fulltext-user');

    $json = $this->getJson('/api/v2/sentence?view=fulltext&key=abc', authHeader($uid))
        ->assertOk()
        ->json();

    expect($json['ok'])->toBeTrue()
        ->and($json['data'])->toHaveKey('rows');
});

it('rejects userdict view=user without a bearer token', function () {
    $json = withV1Cookie(['user_id' => '1'], fn () => $this
        ->getJson('/api/v2/userdict?view=user')
        ->assertStatus(400)
        ->json());

    expect($json['ok'])->toBeFalse()
        ->and($json['message'])->toBe(__('auth.failed'));
});

it('serves userdict view=user to a bearer-authenticated user', function () {
    $uid = makeStudio('userdict-user');

    $json = $this->getJson('/api/v2/userdict?view=user', authHeader($uid))
        ->assertOk()
        ->json();

    expect($json['ok'])->toBeTrue()
        ->and($json['data'])->toHaveKey('rows');
});

it('rejects DELETE /v2/userdict without a bearer token', function () {
    $json = withV1Cookie(['user_id' => '1'], fn () => $this
        ->deleteJson('/api/v2/userdict', ['id' => json_encode(['1'])])
        ->assertStatus(400)
        ->json());

    expect($json['ok'])->toBeFalse()
        ->and($json['message'])->toBe(__('auth.failed'));
});
