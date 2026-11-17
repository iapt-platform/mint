<?php

use \WikipaliApi\Api\GlossariesApi;

/*
 * 术语词汇表：GET /v3/glossaries/{grammar|community}（取代 v2 term-vocabulary）。只读、整表、公开。
 * 各服务器的数据量不同，只核对形状与回退规则；排序、已删条目、不暴露自增 id 由 api-v13 的 GlossaryV3Test 覆盖。
 */

test('GET /v3/glossaries/community?lang=zh-Hans → 200，整表带 meta', function () {
    $res = request(fn () => api(GlossariesApi::class)->getApiV3GlossariesKindWithHttpInfo('community', 'zh-Hans'));
    assert_status(200, $res, 'community glossary');
    foreach (['kind', 'lang', 'total'] as $key) {
        assert_has_key($key, $res['body']['meta'], 'glossary meta');
    }
    assert_eq('community', $res['body']['meta']['kind'], 'meta.kind');
    assert_eq(count($res['body']['data']), $res['body']['meta']['total'], '整表不分页：total 与条数相等');
    $first = $res['body']['data'][0] ?? null;
    if ($first) {
        assert_eq(['id', 'word', 'tag', 'meaning', 'other_meaning'], array_keys($first), '条目字段');
    }
});

test('GET /v3/glossaries/grammar?lang=xx（没有这种语言的表）→ 200，回退到 en', function () {
    $res = raw('GET', '/v3/glossaries/grammar', ['lang' => 'xx']);
    assert_status(200, $res, 'grammar glossary 回退');
    assert_eq('en', $res['body']['meta']['lang'] ?? null, '回退语言');
});

test('GET /v3/glossaries/{非法 kind} → 404；lang 格式不对 → 422', function () {
    assert_status(404, raw('GET', '/v3/glossaries/other'), '非法 kind');
    $bad = raw('GET', '/v3/glossaries/community', ['lang' => '../x']);
    assert_status(422, $bad, 'lang 格式不对');
    assert_field_error('lang', $bad, 'lang 格式不对');
});
