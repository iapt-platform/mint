<?php

/*
 * 按词形找段落：GET /v3/tipitaka-word-paragraphs (+ /aggregate)（取代 v2 search-pali-wbw / -books）。只读、公开。
 * 各服务器数据不同，只核对形状与几条不变量；高亮转义、N+1、黑体过滤由 api-v13 的 TipitakaWordParagraphV3Test 覆盖。
 */

const WORD_FORMS = 'dhammo,dhammaṃ,dhammassa';

test('GET /v3/tipitaka-word-paragraphs → 200，分页、字段完整', function () {
    $res = raw('GET', '/v3/tipitaka-word-paragraphs', ['words' => WORD_FORMS, 'per_page' => 3]);
    assert_status(200, $res, 'word paragraphs');
    foreach (['current_page', 'per_page', 'total', 'last_page'] as $key) {
        assert_has_key($key, $res['body']['meta'], 'meta');
    }
    $row = $res['body']['data'][0] ?? null;
    assert_eq(true, $row !== null, '常见词至少命中一段');
    assert_eq(['id', 'book', 'para', 'score', 'hits', 'title', 'level', 'path', 'text', 'refs'], array_keys($row), '段落字段');
    assert_eq("{$row['book']}-{$row['para']}", $row['id'], 'id 是 book-para');
    assert_eq('WP', end($row['refs'])['type'], '最后一条 ref 是 WP 段落号');
});

test('GET /v3/tipitaka-word-paragraphs/aggregate → 每本书的段落数之和等于列表总数', function () {
    $agg = raw('GET', '/v3/tipitaka-word-paragraphs/aggregate', ['words' => WORD_FORMS, 'bold' => 'only']);
    assert_status(200, $agg, 'aggregate');
    $list = raw('GET', '/v3/tipitaka-word-paragraphs', ['words' => WORD_FORMS, 'bold' => 'only', 'per_page' => 1]);
    assert_eq($list['body']['meta']['total'], $agg['body']['meta']['paragraphs'], '段落总数一致');
    $book = $agg['body']['data'][0] ?? null;
    if ($book) {
        assert_eq(['book_id', 'book', 'para', 'title', 'layer', 'tags', 'paragraphs', 'occurrences'], array_keys($book), '书字段');
        $one = raw('GET', '/v3/tipitaka-word-paragraphs', ['words' => WORD_FORMS, 'bold' => 'only', 'book_id' => $book['book_id'], 'per_page' => 1]);
        assert_eq($book['paragraphs'], $one['body']['meta']['total'], '按 book_id 过滤后的总数等于该书的段落数');
    }
});

test('GET /v3/tipitaka-word-paragraphs 缺 words / 空词形 / bold 不对 → 422', function () {
    assert_status(422, raw('GET', '/v3/tipitaka-word-paragraphs'), '缺 words');
    assert_status(422, raw('GET', '/v3/tipitaka-word-paragraphs', ['words' => ',,']), '空词形');
    assert_status(422, raw('GET', '/v3/tipitaka-word-paragraphs', ['words' => 'x', 'bold' => 'on']), 'bold=on 是 v2 写法');
    assert_status(422, raw('GET', '/v3/tipitaka-word-paragraphs/aggregate'), 'aggregate 缺 words');
});
