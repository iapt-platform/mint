<?php

use \WikipaliApi\Api\CreditPolicyApi;

test('GET /v3/credit-policy → 200 公开，无需登录', function () {
    $res = request(fn () => api(CreditPolicyApi::class)->getApiV3CreditPolicyWithHttpInfo());
    assert_status(200, $res, 'credit-policy');
    $data = $res['body']['data'] ?? null;
    foreach (['version', 'day_timezone', 'daily_free', 'cost', 'guest_ip_daily_cap', 'earn'] as $key) {
        assert_has_key($key, $data, 'credit-policy.data');
    }
    // 版本号是生效日 YYYYMMDD
    assert_eq(1, preg_match('/^20\d{6}$/', (string) $data['version']), 'credit-policy.data.version 是 YYYYMMDD');
    assert_eq('UTC', $data['day_timezone'], 'credit-policy.data.day_timezone');
    assert_has_key('normal', $data['cost'], 'credit-policy.data.cost');
    assert_has_key('deep', $data['cost'], 'credit-policy.data.cost');
});
