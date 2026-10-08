<?php

use \WikipaliApi\Api\EmailCertificationsApi;
use \WikipaliApi\Api\InvitesApi;
use \WikipaliApi\Api\MeApi;
use \WikipaliApi\Api\PasswordResetsApi;
use \WikipaliApi\Api\SessionsApi;
use \WikipaliApi\Api\UsersApi;
use \WikipaliApi\Model\PatchApiV3PasswordResetsTokenRequest;
use \WikipaliApi\Model\PostApiV3InvitesRequest;
use \WikipaliApi\Model\PostApiV3PasswordResetsRequest;
use \WikipaliApi\Model\PostApiV3SessionsRequest;
use \WikipaliApi\Model\PostApiV3UsersRequest;

/*
 * 账号：登录、我是谁、找回密码、注册。除 /v3/me 外全部匿名端点。
 *
 * ⚠️ 这些用例会在所有服务器（含 prod）上真实执行，所以**只走不产生副作用的路径**：
 *   - 不给任何真实邮箱发信：找回密码只用未注册的 `.invalid` 邮箱（服务端什么都不发）；
 *     注册验证码只测参数校验失败（发码的成功路径会真的发邮件，不测）。
 *   - 不建账号：POST /v3/users 只用不存在的 invite，在建号之前就被拒。
 * 完整的成功路径（收码 → 换 invite → 建号 → 重置密码）由 api-v13 的 Pest 测试覆盖
 * （tests/Feature/SignUpV3Test.php、PasswordResetV3Test.php），那里 Mail 是 fake 的。
 *
 * 发信类端点有限流（同 IP 每分钟 10 次、同邮箱每小时 5 次），本文件每次跑只打几次，
 * 但短时间内反复跑全量可能撞到 429。
 */

/** 每次运行不同的未注册邮箱：`.invalid` 是 RFC 2606 保留域，不会有人收到信 */
function auth_unregistered_email(): string
{
    static $email = null;

    return $email ??= 'api-test-' . bin2hex(random_bytes(6)) . '@example.invalid';
}

/**
 * 格式不合法的邮箱，每次运行不同。
 *
 * 发信类端点还按邮箱每小时限 5 次，而且限流先于参数校验执行——写死一个
 * `not-an-email` 的话，一小时内跑到第 6 遍就从 422 变成 429。
 */
function auth_malformed_email(): string
{
    return 'not-an-email-' . bin2hex(random_bytes(4));
}

/** 格式合法但不可能存在的 reset token（64 位字母数字） */
const AUTH_UNKNOWN_RESET_TOKEN = 'apitest000000000000000000000000000000000000000000000000000000000';

/* ---- /v3/password-resets ---- */

test('POST /v3/password-resets（未注册邮箱）→ 204，不泄露账号是否存在', function () {
    $body = new PostApiV3PasswordResetsRequest();
    $body->setEmail(auth_unregistered_email());
    $body->setLang('zh-Hans');
    $res = request(fn () => api(PasswordResetsApi::class)->postApiV3PasswordResetsWithHttpInfo($body));
    assert_status(204, $res, 'password-resets 未注册邮箱');
});

test('POST /v3/password-resets（邮箱格式错）→ 422 errors.email', function () {
    $res = raw('POST', '/v3/password-resets', null, ['email' => auth_malformed_email()]);
    assert_field_error('email', $res, 'password-resets 邮箱格式错');
});

test('POST /v3/password-resets（缺 email）→ 422 errors.email', function () {
    $res = raw('POST', '/v3/password-resets', null, []);
    assert_field_error('email', $res, 'password-resets 缺 email');
});

test('GET /v3/password-resets/{token}（不存在）→ 404', function () {
    $res = request(fn () => api(PasswordResetsApi::class)->getApiV3PasswordResetsTokenWithHttpInfo(AUTH_UNKNOWN_RESET_TOKEN));
    assert_status(404, $res, 'password-resets show 不存在');
    assert_problem($res, 'password-resets show 不存在');
    // 业务 404（token 无效）而不是路由 404（路径没匹配上）——后者说明测试自己的 token 格式写错了
    if (str_contains((string) ($res['body']['detail'] ?? ''), 'could not be found')) {
        fail('password-resets show 不存在：拿到的是路由 404，检查 AUTH_UNKNOWN_RESET_TOKEN 是否正好 64 位字母数字');
    }
});

test('GET /v3/password-resets/{token}（格式不对）→ 404', function () {
    // 路由约束是 64 位字母数字，匹配不上任何路由 → 404（不是 422）
    $res = raw('GET', '/v3/password-resets/short-token');
    assert_status(404, $res, 'password-resets show 格式不对');
    assert_problem($res, 'password-resets show 格式不对');
});

test('PATCH /v3/password-resets/{token}（不存在）→ 404', function () {
    $body = new PatchApiV3PasswordResetsTokenRequest();
    $body->setPassword('api-test-pw');
    $body->setPasswordConfirmation('api-test-pw');
    $res = request(fn () => api(PasswordResetsApi::class)->patchApiV3PasswordResetsTokenWithHttpInfo(AUTH_UNKNOWN_RESET_TOKEN, $body));
    assert_status(404, $res, 'password-resets update 不存在');
    assert_problem($res, 'password-resets update 不存在');
});

test('PATCH /v3/password-resets/{token}（两次密码不一致）→ 422 errors.password', function () {
    // 入参校验先于 token 查找，所以 token 不存在也是 422
    $res = raw('PATCH', '/v3/password-resets/' . AUTH_UNKNOWN_RESET_TOKEN, null, [
        'password' => 'api-test-pw',
        'password_confirmation' => 'api-test-PW',
    ]);
    assert_field_error('password', $res, 'password-resets update 确认不一致');
});

test('PUT /v3/password-resets/{token} → 405（只注册了 PATCH）', function () {
    $res = raw('PUT', '/v3/password-resets/' . AUTH_UNKNOWN_RESET_TOKEN, null, []);
    assert_status(405, $res, 'password-resets PUT');
});

/* ---- /v3/email-certifications（只测校验失败：成功路径会真的发邮件）---- */

test('POST /v3/email-certifications（邮箱格式错）→ 422 errors.email', function () {
    $res = raw('POST', '/v3/email-certifications', null, ['email' => auth_malformed_email()]);
    assert_field_error('email', $res, 'email-certifications 邮箱格式错');
});

test('POST /v3/email-certifications（已注册邮箱）→ 422 errors.email', function () {
    $email = fixtures()['registered_email'] ?? null;
    if (! $email) {
        skip('fixtures.php 未配置 registered_email');
    }
    $res = raw('POST', '/v3/email-certifications', null, ['email' => $email]);
    assert_field_error('email', $res, 'email-certifications 已注册邮箱');
});

/* ---- /v3/invites ---- */

test('POST /v3/invites（没发过码的邮箱）→ 422 errors.code', function () {
    $body = new PostApiV3InvitesRequest();
    $body->setEmail(auth_unregistered_email());
    $body->setCode('000000');
    $res = request(fn () => api(InvitesApi::class)->postApiV3InvitesWithHttpInfo($body));
    assert_field_error('code', $res, 'invites 无效验证码');
});

test('POST /v3/invites（验证码不是 6 位数字）→ 422 errors.code', function () {
    $res = raw('POST', '/v3/invites', null, ['email' => auth_unregistered_email(), 'code' => '12ab']);
    assert_field_error('code', $res, 'invites 验证码格式错');
});

test('GET /v3/invites/{invite}（不存在）→ 404', function () {
    $res = request(fn () => api(InvitesApi::class)->getApiV3InvitesInviteWithHttpInfo('00000000-0000-4000-8000-000000000000'));
    assert_status(404, $res, 'invites show 不存在');
    assert_problem($res, 'invites show 不存在');
});

test('GET /v3/invites/{invite}（不是 uuid）→ 404', function () {
    $res = raw('GET', '/v3/invites/not-a-uuid');
    assert_status(404, $res, 'invites show 非 uuid');
    assert_problem($res, 'invites show 非 uuid');
});

/* ---- /v3/users（只用不存在的 invite：不会建出账号）---- */

test('POST /v3/users（invite 不存在）→ 422 errors.invite', function () {
    $body = new PostApiV3UsersRequest();
    $body->setInvite('00000000-0000-4000-8000-000000000000');
    $body->setUsername('apitest_' . bin2hex(random_bytes(4)));
    $body->setPassword('api-test-pw');
    $body->setPasswordConfirmation('api-test-pw');
    $body->setLang('zh-Hans');
    $res = request(fn () => api(UsersApi::class)->postApiV3UsersWithHttpInfo($body));
    assert_field_error('invite', $res, 'users invite 不存在');
});

test('POST /v3/users（字段不合法）→ 422，逐字段报错', function () {
    $res = raw('POST', '/v3/users', null, [
        'invite' => 'not-a-uuid',
        'username' => 'abc中文',
        'password' => '123',
        'password_confirmation' => '456',
    ]);
    foreach (['invite', 'username', 'password', 'lang'] as $field) {
        assert_field_error($field, $res, "users 字段不合法（{$field}）");
    }
});

/* ---- /v3/sessions、/v3/me ---- */
// 登录按「账号 + IP」每分钟限 5 次：带账号跑时 run.php 已经登录过一次，这里再登一次，
// 错误密码只用不存在的账号（不占测试账号的额度）。

test('POST /v3/sessions（正确账号）→ 201 {token, user}', function () {
    $username = config()['username'] ?? null;
    $password = config()['password'] ?? null;
    if (! $username || ! $password) {
        skip('未给 --username/--password');
    }
    $body = new PostApiV3SessionsRequest();
    $body->setLogin($username);
    $body->setPassword($password);
    $res = request(fn () => api(SessionsApi::class)->postApiV3SessionsWithHttpInfo($body));
    assert_status(201, $res, 'sessions 登录');
    $data = $res['body']['data'] ?? null;
    assert_has_key('token', $data, 'sessions.data');
    assert_has_key('user', $data, 'sessions.data');
    foreach (['id', 'nickName', 'userName', 'roles', 'email'] as $key) {
        assert_has_key($key, $data['user'], 'sessions.data.user');
    }
    if (array_key_exists('password', $data['user'])) {
        fail('sessions.data.user 不应包含 password');
    }
});

test('POST /v3/sessions（账号不存在）→ 422 errors.login', function () {
    $res = raw('POST', '/v3/sessions', null, [
        'login' => 'apitest_nobody_' . bin2hex(random_bytes(4)),
        'password' => 'whatever',
    ]);
    assert_field_error('login', $res, 'sessions 账号不存在');
});

test('POST /v3/sessions（缺字段）→ 422 errors.login + errors.password', function () {
    $res = raw('POST', '/v3/sessions', null, []);
    assert_field_error('login', $res, 'sessions 缺字段');
    assert_field_error('password', $res, 'sessions 缺字段');
});

test('GET /v3/me（无 token）→ 401', function () {
    $res = request(fn () => api(MeApi::class)->getApiV3MeWithHttpInfo());
    assert_status(401, $res, 'me 无 token');
    assert_problem($res, 'me 无 token');
});

test('GET /v3/me（带 token）→ 200，不回显 token', function () {
    $res = request(fn () => api(MeApi::class, required_token())->getApiV3MeWithHttpInfo());
    assert_status(200, $res, 'me');
    $data = $res['body']['data'] ?? null;
    foreach (['id', 'nickName', 'userName', 'realName', 'avatar', 'roles', 'email'] as $key) {
        assert_has_key($key, $data, 'me.data');
    }
    if (array_key_exists('token', $data)) {
        fail('me.data 不应回显 token（v2 auth/current 会）');
    }
});

/* ---- 语言协商（v3 按 Accept-Language 本地化 detail / errors，title 保持英文）---- */
// 探针用「错验证码换 invite」：不写库、不发信，走 sign-up 限流（同 IP 每分钟 20 次），
// 不占登录的「账号 + IP」额度；返回的是 messages.php 里的文案。

/** 用指定 Accept-Language 发一次错验证码请求 */
function auth_wrong_code_in(string $acceptLanguage): array
{
    return raw('POST', '/v3/invites', null, [
        'email' => auth_unregistered_email(),
        'code' => '000000',
    ], null, ['Accept-Language' => $acceptLanguage]);
}

test('Accept-Language: zh-Hans → 中文错误文案 + Content-Language', function () {
    $res = auth_wrong_code_in('zh-Hans');
    assert_field_error('code', $res, 'invites 错验证码（zh-Hans）');
    assert_eq('验证码不正确或已过期。', $res['body']['errors']['code'][0] ?? null, 'errors.code');
    assert_eq('zh-Hans', $res['headers']['Content-Language'][0] ?? null, 'Content-Language');
});

test('Accept-Language: zh-TW → 繁体；不支持的语言 → en', function () {
    $hant = auth_wrong_code_in('zh-TW');
    assert_eq('驗證碼不正確或已過期。', $hant['body']['errors']['code'][0] ?? null, 'zh-TW 的 errors.code');
    $fr = auth_wrong_code_in('fr-FR');
    assert_eq('en', $fr['headers']['Content-Language'][0] ?? null, 'fr-FR 的 Content-Language');
});
