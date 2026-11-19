<?php

/*
 * 按印本页码找段落：GET /v3/tipitaka-page-numbers/{page}（取代 v2 search-page-number）。只读、公开。
 * 各服务器数据不同，只核对形状与过滤的不变量；N+1、排序、卷号 0 由 api-v13 的 TipitakaPageNumberV3Test 覆盖。
 */

test('GET /v3/tipitaka-page-numbers/{page} → 200，字段完整，meta.total 等于条数', function () {
    $res = raw('GET', '/v3/tipitaka-page-numbers/100');
    assert_status(200, $res, 'page numbers');
    assert_eq(count($res['body']['data']), $res['body']['meta']['total'], 'total 等于条数（不分页）');
    $row = $res['body']['data'][0] ?? null;
    assert_eq(true, $row !== null, '第 100 页至少有一处');
    assert_eq(['edition', 'volume', 'page', 'book', 'para', 'book_id', 'book_title', 'book_para', 'edition_title', 'layer', 'tags'], array_keys($row), '字段');
});

test('GET /v3/tipitaka-page-numbers/{page}?edition=&volume= → 只要这个版本这一卷', function () {
    $res = raw('GET', '/v3/tipitaka-page-numbers/100', ['edition' => 'PTS', 'volume' => 2]);
    assert_status(200, $res, 'filtered');
    foreach ($res['body']['data'] as $row) {
        assert_eq(['PTS', 2], [$row['edition'], $row['volume']], '过滤生效');
    }
});

test('GET /v3/tipitaka-page-numbers 版本不对 / 卷号 0 → 422；页码不是数字 → 404', function () {
    assert_status(422, raw('GET', '/v3/tipitaka-page-numbers/100', ['edition' => 'P']), 'edition=P 是 v2 的单字母');
    assert_status(422, raw('GET', '/v3/tipitaka-page-numbers/100', ['volume' => 0]), 'volume=0');
    assert_status(404, raw('GET', '/v3/tipitaka-page-numbers/abc'), '路径约束');
});
