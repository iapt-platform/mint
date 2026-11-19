<?php

use \WikipaliApi\Api\SearchApi;
use \WikipaliApi\Api\SearchSuggestionsApi;
use \WikipaliApi\Model\PostApiV3SearchRequest;

/** 一条命中必须有的字段（v3 归一化后的形状，不再透出 OpenSearch 的 _source） */
const SEARCH_HIT_KEYS = ['id', 'resource_type', 'resource_id', 'granularity', 'language', 'related_id',
    'score', 'title', 'content', 'highlight', 'bold', 'path', 'category', 'tags', 'updated_at'];

function assert_search_page(array $res, string $what): void
{
    assert_has_key('data', $res['body'], $what);
    assert_has_key('meta', $res['body'], $what);
    foreach (['current_page', 'per_page', 'total', 'total_relation', 'last_page'] as $key) {
        assert_has_key($key, $res['body']['meta'], "{$what} meta");
    }
    if (! is_array($res['body']['data']) || $res['body']['data'] === []) {
        fail("{$what}：data 应是非空列表");
    }
    foreach (SEARCH_HIT_KEYS as $key) {
        assert_has_key($key, $res['body']['data'][0], "{$what} data[0]");
    }
    if (array_key_exists('_source', $res['body']['data'][0])) {
        fail("{$what}：不应再透出 _source");
    }
}

test('GET /v3/search?q=dukkha → 200 {data, meta}', function () {
    $res = request(fn () => api(SearchApi::class)->getApiV3SearchWithHttpInfo(
        fixtures()['search_q'], 'fuzzy', 'tipitaka', null, 'paragraph', null, null, null, null, null, null, null, false, 1, 5));
    assert_status(200, $res, 'search GET');
    assert_search_page($res, 'search GET');
    assert_eq(5, $res['body']['meta']['per_page'], 'search GET per_page');
    if (array_key_exists('facets', $res['body']['meta'])) {
        fail('search GET：没要 facets 就不应返回');
    }
});

test('GET /v3/search?facets=true → meta.facets 分面计数', function () {
    $res = raw('GET', '/v3/search', ['q' => fixtures()['search_q'], 'per_page' => 1, 'facets' => 'true']);
    assert_status(200, $res, 'search facets');
    assert_has_key('facets', $res['body']['meta'], 'search facets');
    assert_has_key('resource_type', $res['body']['meta']['facets'], 'search facets');
});

test('POST /v3/search → 200（与 GET 等价，参数在 body）', function () {
    $body = new PostApiV3SearchRequest();
    $body->setQ(fixtures()['search_q']);
    $body->setPerPage(3);
    $res = request(fn () => api(SearchApi::class)->postApiV3SearchWithHttpInfo($body));
    assert_status(200, $res, 'search POST');
    assert_search_page($res, 'search POST');
});

test('GET /v3/search（缺 q / 非法模式 / 翻页过深）→ 422', function () {
    assert_field_error('q', raw('GET', '/v3/search'), 'search 缺 q');
    assert_field_error('search_mode', raw('GET', '/v3/search', ['q' => 'x', 'search_mode' => 'bogus']), 'search 非法模式');
    assert_field_error('page', raw('GET', '/v3/search', ['q' => 'x', 'per_page' => 100, 'page' => 101]), 'search 翻页过深');
});

test('GET /v3/search/{id} → 200（取检索结果里的第一条）', function () {
    // 文档 id 从检索结果里拿，不依赖各环境的种子数据
    $hits = raw('GET', '/v3/search', ['q' => fixtures()['search_q'], 'per_page' => 1]);
    assert_status(200, $hits, 'search show 前置检索');
    $id = $hits['body']['data'][0]['id'] ?? null;
    if (! is_string($id)) {
        fail('search show：检索结果为空，拿不到文档 id');
    }
    $res = request(fn () => api(SearchApi::class)->getApiV3SearchSearchWithHttpInfo($id));
    assert_status(200, $res, 'search show');
    assert_eq($id, $res['body']['data']['id'] ?? null, 'search show id');
});

test('GET /v3/search/{id} → 404（不存在的文档）', function () {
    $res = request(fn () => api(SearchApi::class)->getApiV3SearchSearchWithHttpInfo('no_such_doc_xyz'));
    assert_status(404, $res, 'search show 404');
    assert_problem($res, 'search show 404');
});

test('GET /v3/search-suggestions?q=dukkha → 200 {data, meta}', function () {
    $res = request(fn () => api(SearchSuggestionsApi::class)->getApiV3SearchSuggestionsWithHttpInfo(fixtures()['search_q'], null, 5));
    assert_status(200, $res, 'search-suggestions');
    assert_has_key('data', $res['body'], 'search-suggestions');
    assert_has_key('total', $res['body']['meta'] ?? null, 'search-suggestions meta');
    if (count($res['body']['data']) > 5) {
        fail('search-suggestions：limit=5 却返回了 '.count($res['body']['data']).' 条');
    }
    $texts = array_map(fn ($s) => mb_strtolower($s['text']), $res['body']['data']);
    if (count($texts) !== count(array_unique($texts))) {
        fail('search-suggestions：同一个词出现了不止一次');
    }
    foreach ($res['body']['data'] as $s) {
        foreach (['text', 'source', 'score', 'doc_id', 'resource_type', 'language', 'granularity', 'category'] as $key) {
            assert_has_key($key, $s, 'search-suggestions 条目');
        }
    }
});

test('GET /v3/search-suggestions（缺 q / 未知字段）→ 422', function () {
    assert_field_error('q', raw('GET', '/v3/search-suggestions', ['q' => '']), 'search-suggestions 空 q');
    assert_field_error('fields', raw('GET', '/v3/search-suggestions', ['q' => 'd', 'fields' => 'title']), 'search-suggestions 未知字段');
});
