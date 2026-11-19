<?php

/*
 * 巴利词补全：GET /v3/pali-word-suggestions?q=（取代 v2 pali-word-index?view=key）。只读、公开。
 * 各服务器的词频不同，只核对形状与排序规则；通配符、去变音匹配由 api-v13 的 PaliWordSuggestionV3Test 覆盖。
 */

test('GET /v3/pali-word-suggestions → 200，都以前缀开头，与输入相同的在前、其余按次数降序', function () {
    $res = raw('GET', '/v3/pali-word-suggestions?q=vitakk&limit=5');
    assert_status(200, $res, 'suggestions');
    $data = $res['body']['data'];
    assert_eq(count($data), $res['body']['meta']['total'], 'meta.total 等于条数');
    assert_eq(true, count($data) > 0 && count($data) <= 5, 'limit 生效');
    assert_eq(['word', 'count', 'bold'], array_keys($data[0]), '字段');
    foreach ($data as $w) {
        assert_eq(true, str_starts_with($w['word'], 'vitakk'), "{$w['word']} 以前缀开头");
    }
    $counts = array_column($data, 'count');
    $sorted = $counts;
    rsort($sorted);
    assert_eq($sorted, $counts, '按出现次数降序');
});

test('GET /v3/pali-word-suggestions 缺 q / limit 越界 → 422', function () {
    assert_status(422, raw('GET', '/v3/pali-word-suggestions'), '缺 q');
    assert_status(422, raw('GET', '/v3/pali-word-suggestions?q=a&limit=51'), 'limit 越界');
});
