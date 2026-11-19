<?php

/*
 * 词形展开：GET /v3/pali-word-forms/{word}（取代 v2 case/{word}）。只读、公开。
 * 各服务器的词频不同，只核对形状；N+1、大小写、排序由 api-v13 的 PaliWordFormV3Test 覆盖。
 */

test('GET /v3/pali-word-forms/{word} → 200，第一组是输入的词本身', function () {
    $res = raw('GET', '/v3/pali-word-forms/'.rawurlencode('dhamma'));
    assert_status(200, $res, 'word forms');
    assert_has_key('total', $res['body']['meta'], 'meta');
    $first = $res['body']['data'][0] ?? null;
    assert_eq(true, $first !== null, '常见词至少有一组');
    assert_eq('dhamma', $first['base'], '第一组是输入的词');
    assert_eq(['word', 'count', 'bold'], array_keys($first['forms'][0]), '词形字段');
});

test('GET /v3/pali-word-forms/{不存在的词} → 200 空列表；过长 → 404', function () {
    $res = raw('GET', '/v3/pali-word-forms/qqqzzzxx');
    assert_status(200, $res, '空列表');
    assert_eq([], $res['body']['data'], '没有词形');
    $long = raw('GET', '/v3/pali-word-forms/'.str_repeat('a', 65));
    assert_status(404, $long, '路径约束');
});
