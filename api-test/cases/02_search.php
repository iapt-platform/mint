<?php

use \WikipaliApi\Api\SearchApi;
use \WikipaliApi\Api\SearchSuggestApi;
use \WikipaliApi\Model\PostApiV3SearchRequest;

test('GET /v3/search?q=dukkha → 200', function () {
    $res = request(fn () => api(SearchApi::class)->getApiV3SearchWithHttpInfo(fixtures()['search_q']));
    assert_status(200, $res, 'search GET');
    // spec 说 data 是数组，实际是 {success,data:{took,hits}}（漂移，见 README）
    assert_has_key('data', $res['body'], 'search GET');
});

test('POST /v3/search → 200（与 GET 等价，参数在 body）', function () {
    $body = new PostApiV3SearchRequest();
    $body->setQ(fixtures()['search_q']);
    $res = request(fn () => api(SearchApi::class)->postApiV3SearchWithHttpInfo($body));
    assert_status(200, $res, 'search POST');
    assert_has_key('data', $res['body'], 'search POST');
});

test('GET /v3/search/{id} → 200（真实文档 id）', function () {
    $res = request(fn () => api(SearchApi::class)->getApiV3SearchSearchWithHttpInfo(fixtures()['search_doc_id']));
    assert_status(200, $res, 'search show');
    assert_has_key('data', $res['body'], 'search show');
});

test('GET /v3/search/{id} → 404（不存在的文档）', function () {
    $res = request(fn () => api(SearchApi::class)->getApiV3SearchSearchWithHttpInfo('no_such_doc_xyz'));
    assert_status(404, $res, 'search show 404');
    assert_problem($res, 'search show 404');
});

test('GET /v3/search-suggest?q=dukkha → 200', function () {
    $res = request(fn () => api(SearchSuggestApi::class)->getApiV3SearchSuggestWithHttpInfo(fixtures()['search_q']));
    assert_status(200, $res, 'search-suggest');
    assert_has_key('data', $res['body'], 'search-suggest');
});

test('GET /v3/search-suggest?q= → 400（空查询）', function () {
    // 描述里写明「q 为空返回 400」；spec 只登记了 422，这里按实际 400 断言。
    $res = raw('GET', '/v3/search-suggest', ['q' => '']);
    assert_status(400, $res, 'search-suggest 空 q');
});
