<?php

use \WikipaliApi\Api\ProgressApi;

test('GET /v3/progress?channels=… → 200', function () {
    $res = request(fn () => api(ProgressApi::class)->getApiV3ProgressWithHttpInfo(fixtures()['channel']));
    assert_status(200, $res, 'progress');
    assert_has_key('data', $res['body'], 'progress');
    assert_has_key('meta', $res['body'], 'progress');
});

test('GET /v3/progress（缺 channels）→ 422', function () {
    $res = raw('GET', '/v3/progress');
    assert_status(422, $res, 'progress 缺 channels');
    assert_problem($res, 'progress 缺 channels');
});

test('GET /v3/progress?order=非法值 → 422', function () {
    $res = raw('GET', '/v3/progress', ['channels' => fixtures()['channel'], 'order' => 'bogus']);
    assert_status(422, $res, 'progress 非法 order');
    assert_problem($res, 'progress 非法 order');
});
