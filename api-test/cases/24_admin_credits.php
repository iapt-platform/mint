<?php

use \WikipaliApi\Api\AdminApi;
use \WikipaliApi\Model\PostApiV3AdminCreditAdjustmentsRequest;
use \WikipaliApi\Model\PostApiV3AdminCreditSettlementsRequest;

/*
 * v3 管理后台 · AI 对话积分：/v3/admin/credit-overview、credit-accounts、credit-ledger、
 * credit-adjustments、credit-settlements（仅 root / administrator）。
 *
 * 测试账号是普通用户时只测 401 / 403；是管理员时再测读、422 与写。
 * 写都是幂等的：结算重复执行结果覆盖；给自己 +1 再 -1 用固定的 ref，每台服务器只入账一次、净额为 0。
 */

/** 测试账号是不是管理员（看 /v3/me 的 roles） */
function credit_admin(string $token): bool
{
    $me = raw('GET', '/v3/me', null, null, $token);
    $roles = $me['body']['data']['roles'] ?? [];

    return (bool) array_intersect(['administrator', 'root'], $roles);
}

function admin_token_or_skip(): string
{
    $token = required_token();
    if (! credit_admin($token)) {
        skip('测试账号不是管理员');
    }

    return $token;
}

/* ---- 无 token → 401 ---- */

test('GET /v3/admin/credit-overview（无 token）→ 401', function () {
    $res = request(fn () => api(AdminApi::class)->getApiV3AdminCreditOverviewWithHttpInfo());
    assert_status(401, $res, 'credit-overview');
    assert_problem($res, 'credit-overview');
});

test('POST /v3/admin/credit-settlements（无 token）→ 401', function () {
    $res = request(fn () => api(AdminApi::class)->postApiV3AdminCreditSettlementsWithHttpInfo(new PostApiV3AdminCreditSettlementsRequest()));
    assert_status(401, $res, 'credit-settlements');
});

/* ---- 普通用户 → 403 ---- */

test('GET /v3/admin/credit-accounts（非管理员）→ 403', function () {
    $token = required_token();
    if (credit_admin($token)) {
        skip('测试账号是管理员');
    }
    $res = request(fn () => api(AdminApi::class, $token)->getApiV3AdminCreditAccountsWithHttpInfo());
    assert_status(403, $res, 'credit-accounts');
    assert_problem($res, 'credit-accounts');
});

/* ---- 管理员：读 ---- */

test('GET /v3/admin/credit-overview → 200', function () {
    $res = request(fn () => api(AdminApi::class, admin_token_or_skip())->getApiV3AdminCreditOverviewWithHttpInfo());
    assert_status(200, $res, 'credit-overview');
    foreach (['today', 'earn_since', 'policy_version', 'policy', 'totals', 'recent_days', 'last_settlement'] as $key) {
        assert_has_key($key, $res['body']['data'], 'credit-overview.data');
    }
    assert_eq(14, count($res['body']['data']['recent_days']), 'recent_days 14 天');
});

test('GET /v3/admin/credit-accounts → 200 分页', function () {
    $res = request(fn () => api(AdminApi::class, admin_token_or_skip())->getApiV3AdminCreditAccountsWithHttpInfo(per_page: 5));
    assert_status(200, $res, 'credit-accounts');
    assert_has_key('total', $res['body']['meta'], 'credit-accounts.meta');
    foreach ($res['body']['data'] as $row) {
        foreach (['account_type', 'account_id', 'earned_balance', 'spent_total', 'free_used_today', 'daily_free'] as $key) {
            assert_has_key($key, $row, 'credit-accounts.data[]');
        }
    }
});

test('GET /v3/admin/credit-ledger（只给 account_id）→ 422', function () {
    $res = request(fn () => api(AdminApi::class, admin_token_or_skip())->getApiV3AdminCreditLedgerWithHttpInfo(
        filter_account_id: '00000000-0000-4000-8000-000000000000',
    ));
    assert_status(422, $res, 'credit-ledger');
    assert_field_error('filter.account_type', $res, 'credit-ledger');
});

/* ---- 管理员：写（幂等） ---- */

test('POST /v3/admin/credit-settlements → 201（可重复执行）', function () {
    $res = request(fn () => api(AdminApi::class, admin_token_or_skip())->postApiV3AdminCreditSettlementsWithHttpInfo(
        new PostApiV3AdminCreditSettlementsRequest(),
    ));
    assert_status(201, $res, 'credit-settlements');
    assert_eq('missing', $res['body']['data']['mode'], 'credit-settlements.mode');
});

test('POST /v3/admin/credit-settlements（未来日期）→ 422', function () {
    $body = new PostApiV3AdminCreditSettlementsRequest();
    $body->setDate(gmdate('Y-m-d', time() + 86400 * 2));
    $res = request(fn () => api(AdminApi::class, admin_token_or_skip())->postApiV3AdminCreditSettlementsWithHttpInfo($body));
    assert_status(422, $res, 'credit-settlements');
    assert_field_error('date', $res, 'credit-settlements');
});

test('POST /v3/admin/credit-adjustments：给自己 +1 再 -1（固定 ref，只入账一次）', function () {
    $token = admin_token_or_skip();
    $me = raw('GET', '/v3/me', null, null, $token)['body']['data']['id'];
    foreach ([['8f0b5a52-3c1e-4e0a-9d7a-5b1f0c2e0001', 1], ['8f0b5a52-3c1e-4e0a-9d7a-5b1f0c2e0002', -1]] as [$ref, $delta]) {
        $body = new PostApiV3AdminCreditAdjustmentsRequest();
        $body->setUser($me);
        $body->setDelta($delta);
        $body->setNote('api-test 幂等写入');
        $body->setRef($ref);
        $res = request(fn () => api(AdminApi::class, $token)->postApiV3AdminCreditAdjustmentsWithHttpInfo($body));
        if (! in_array($res['status'], [200, 201], true)) {
            assert_status(201, $res, "credit-adjustments {$delta}");
        }
        assert_eq($delta, $res['body']['data']['delta'], 'credit-adjustments.delta');
    }
});

test('POST /v3/admin/credit-adjustments（delta 为 0、缺原因）→ 422', function () {
    $body = new PostApiV3AdminCreditAdjustmentsRequest();
    $body->setUser('nobody');
    $body->setDelta(0);
    $body->setNote('');
    $res = request(fn () => api(AdminApi::class, admin_token_or_skip())->postApiV3AdminCreditAdjustmentsWithHttpInfo($body));
    assert_status(422, $res, 'credit-adjustments');
    assert_field_error('delta', $res, 'credit-adjustments');
});
