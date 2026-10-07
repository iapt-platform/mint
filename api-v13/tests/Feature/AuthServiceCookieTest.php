<?php

use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * `AuthService::current()` 不再认 cookie。
 *
 * 曾经的那条回落（拿不到 bearer 就读 `$_COOKIE['user_uid']` 并直接返回）是 v1
 * 时代的遗留，不验签名、不验过期、不查库——浏览器里手工种一个就能冒充任何人。
 *
 * **必须直接操作 `$_COOKIE` 超全局。** Laravel 的 `withUnencryptedCookies()` 只填
 * Symfony Request 的 cookie 袋子，不碰超全局（`Request::create()` 不调
 * `overrideGlobals()`），所以用那个助手写出来的"cookie 测试"是空转的——
 * 不管代码里有没有这条分支都会过。
 */
function withCookieSuperglobal(array $cookies, callable $fn): mixed
{
    $backup = $_COOKIE;
    $_COOKIE = $cookies;
    try {
        return $fn();
    } finally {
        $_COOKIE = $backup;
    }
}

it('authenticates a valid bearer token', function () {
    $uid = (string) Str::uuid();

    $user = AuthService::current(Request::create('/', 'GET', server: [
        'HTTP_AUTHORIZATION' => 'Bearer '.userToken($uid, 7),
    ]));

    expect($user)->toBe(['user_uid' => $uid, 'user_id' => 7]);
});

it('rejects a cookie with no bearer token', function () {
    $result = withCookieSuperglobal(
        ['user_uid' => (string) Str::uuid(), 'user_id' => '1'],
        fn () => AuthService::current(Request::create('/', 'GET'))
    );

    expect($result)->toBeFalse();
});

it('ignores the cookie even when the bearer token is invalid', function () {
    // 旧代码是 if(bearer){...} elseif(cookie){...}，bearer 无效时走的是 if 分支
    // 里的 return false，本来就到不了 cookie；这条守的是将来别有人把顺序改回来
    $result = withCookieSuperglobal(
        ['user_uid' => (string) Str::uuid(), 'user_id' => '1'],
        fn () => AuthService::current(Request::create('/', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer not-a-jwt',
        ]))
    );

    expect($result)->toBeFalse();
});

it('rejects a request with neither bearer nor cookie', function () {
    expect(AuthService::current(Request::create('/', 'GET')))->toBeFalse();
});

it('keeps v2 endpoints unreachable by cookie', function () {
    // 端到端：v2 的 like 列表需要登录才会富化 selected；带着 cookie 打过去，
    // 现在拿不到登录态了
    $uid = makeStudio('cookie-v2');
    $target = (string) Str::uuid();

    $json = withCookieSuperglobal(
        ['user_uid' => $uid, 'user_id' => '1'],
        fn () => $this->getJson("/api/v2/like?view=count&target_id={$target}")->json()
    );

    expect($json['ok'])->toBeTrue();
});
