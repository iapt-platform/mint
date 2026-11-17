<?php

use \WikipaliApi\Api\TipitakaReadingApi;
use \WikipaliApi\Api\TipitakaRelatedParagraphsApi;

test('GET /v3/tipitaka-reading/{channel} → 200（无过滤，取首块）', function () {
    $res = request(fn () => api(TipitakaReadingApi::class)->getApiV3TipitakaReadingChannelWithHttpInfo(fixtures()['channel'], null, null, null, null, null, 2));
    assert_status(200, $res, 'tipitaka-reading');
    assert_has_key('data', $res['body'], 'tipitaka-reading');
    assert_has_key('meta', $res['body'], 'tipitaka-reading');
    // next_cursor 为 null 时（取完）生成模型会把它丢掉，这里只看非空字段。
    assert_eq('para', $res['body']['meta']['page_size_unit'] ?? null, 'tipitaka-reading.meta.page_size_unit');
});

test('GET /v3/tipitaka-reading/{channel}?book=… → 200（带 total）', function () {
    $res = request(fn () => api(TipitakaReadingApi::class)->getApiV3TipitakaReadingChannelWithHttpInfo(fixtures()['channel'], 65, null, null, null, null, 2));
    assert_status(200, $res, 'tipitaka-reading book 过滤');
    assert_has_key('total', $res['body']['meta'] ?? [], 'tipitaka-reading.meta.total');
});

test('GET /v3/tipitaka-reading/{channel}?format=react → 422（v2 的 react 格式不再提供）', function () {
    $res = raw('GET', '/v3/tipitaka-reading/' . fixtures()['channel'], ['format' => 'react']);
    assert_status(422, $res, 'tipitaka-reading format=react');
    assert_field_error('format', $res, 'tipitaka-reading format=react');
});

test('GET /v3/tipitaka-reading/{channel}?book=…&format=text&include=sentences → 200，逐句纯文本不含 HTML', function () {
    $res = raw('GET', '/v3/tipitaka-reading/' . fixtures()['channel'], ['book' => 65, 'page_size' => 2, 'format' => 'text', 'include' => 'sentences']);
    assert_status(200, $res, 'tipitaka-reading text');
    foreach ($res['body']['data'] ?? [] as $paragraph) {
        foreach ($paragraph['sentences'] ?? [] as $sentence) {
            if (preg_match('/<[a-z][^>]*>/i', (string) ($sentence['html'] ?? ''))) {
                fail('tipitaka-reading text：句子里不应有 HTML 标签，html=' . $sentence['html']);
            }
        }
    }
});

test('GET /v3/tipitaka-reading/{非 uuid} → 404', function () {
    $res = raw('GET', '/v3/tipitaka-reading/not-a-uuid');
    assert_status(404, $res, 'tipitaka-reading 非 uuid');
    assert_problem($res, 'tipitaka-reading 非 uuid');
});

test('GET /v3/tipitaka-related-paragraphs?book_name=…&cs_para=… → 200', function () {
    $res = request(fn () => api(TipitakaRelatedParagraphsApi::class)->getApiV3TipitakaRelatedParagraphsWithHttpInfo(
        null, null, fixtures()['book_name'], fixtures()['cs_para']
    ));
    assert_status(200, $res, 'related-paragraphs');
    assert_has_key('data', $res['body'], 'related-paragraphs');
    assert_has_key('count', $res['body']['meta'] ?? [], 'related-paragraphs.meta.count');
});

test('GET /v3/tipitaka-related-paragraphs（两组坐标互斥）→ 422', function () {
    $res = raw('GET', '/v3/tipitaka-related-paragraphs', [
        'book' => fixtures()['book'],
        'para' => fixtures()['para'],
        'book_name' => fixtures()['book_name'],
        'cs_para' => fixtures()['cs_para'],
    ]);
    assert_status(422, $res, 'related-paragraphs 互斥');
    assert_problem($res, 'related-paragraphs 互斥');
});

test('GET /v3/tipitaka-related-paragraphs（两组都没给）→ 422', function () {
    $res = raw('GET', '/v3/tipitaka-related-paragraphs');
    assert_status(422, $res, 'related-paragraphs 缺坐标');
    assert_problem($res, 'related-paragraphs 缺坐标');
});

test('GET /v3/tipitaka-related-paragraphs/aggregate → 200', function () {
    $res = request(fn () => api(TipitakaRelatedParagraphsApi::class)->getApiV3TipitakaRelatedParagraphsAggregateWithHttpInfo());
    assert_status(200, $res, 'aggregate');
    assert_has_key('data', $res['body'], 'aggregate');
    $first = $res['body']['data'][0] ?? null;
    assert_has_key('book_name', $first, 'aggregate.data[0]');
});

test('GET /v3/tipitaka-related-paragraphs/aggregate/{book_name} → 200', function () {
    $res = request(fn () => api(TipitakaRelatedParagraphsApi::class)->getApiV3TipitakaRelatedParagraphsAggregateBookNameWithHttpInfo(fixtures()['book_name']));
    assert_status(200, $res, 'aggregate/{book_name}');
    assert_has_key('data', $res['body'], 'aggregate/{book_name}');
    $first = $res['body']['data'][0] ?? null;
    assert_has_key('cs_para', $first, 'aggregate.data[0]');
});
