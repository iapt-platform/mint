<?php

/*
 * 缅文词尾卡片：GET /v3/nissaya-cards/{ending}（取代 v2 nissaya-card）。只读、公开。
 * 各服务器数据不同，只核对形状；关系的格位译名、翻译建议过滤、回退 en、N+1 由 api-v13 的 NissayaCardV3Test 覆盖。
 */

test('GET /v3/nissaya-cards/{ending} → 200，结构完整', function () {
    $res = raw('GET', '/v3/nissaya-cards/'.rawurlencode('သည်'), ['lang' => 'zh-Hans']);
    assert_status(200, $res, 'nissaya card');
    foreach (['ending', 'lang', 'term', 'relations'] as $key) {
        assert_has_key($key, $res['body']['data'], 'nissaya card');
    }
    $row = $res['body']['data']['relations'][0] ?? null;
    if ($row) {
        assert_eq(['relation', 'relation_label', 'from', 'to', 'category', 'local_endings'], array_keys($row), '关系字段');
    }
});

test('GET /v3/nissaya-cards/{查不到的词尾} → 200 空卡片；lang 格式不对 → 422', function () {
    $res = raw('GET', '/v3/nissaya-cards/no-such-ending', ['lang' => 'zh-Hans']);
    assert_status(200, $res, '空卡片');
    assert_eq(null, $res['body']['data']['term'], '没有术语');
    assert_eq([], $res['body']['data']['relations'], '没有关系');
    $bad = raw('GET', '/v3/nissaya-cards/x', ['lang' => '../x']);
    assert_status(422, $bad, 'lang 格式不对');
});
