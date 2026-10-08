<?php

use \WikipaliApi\Api\HeartbeatApi;
use \WikipaliApi\Api\UpgradeApi;

test('GET /v3/heartbeat → 200 {data:{status,checked_at}}', function () {
    $res = request(fn () => api(HeartbeatApi::class)->getApiV3HeartbeatWithHttpInfo());
    assert_status(200, $res, 'heartbeat');
    $data = $res['body']['data'] ?? null;
    assert_has_key('status', $data, 'heartbeat.data');
    assert_eq('ok', $data['status'], 'heartbeat.data.status');
    assert_has_key('checked_at', $data, 'heartbeat.data');
});

test('GET /v3/upgrade → 200 {data:{status}}', function () {
    // 注意：spec 把 data 写成 array，实际返回 {data:{status:"ok"}}（见 README 漂移说明）。
    $res = request(fn () => api(UpgradeApi::class)->getApiV3UpgradeWithHttpInfo());
    assert_status(200, $res, 'upgrade');
    assert_has_key('data', $res['body'], 'upgrade');
});
