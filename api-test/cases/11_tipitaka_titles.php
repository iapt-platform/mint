<?php

use \WikipaliApi\Api\TipitakaTitlesApi;

/*
 * 章节标题：GET /v3/tipitaka-titles
 *
 * 只读、无写操作。用长部《大般涅槃经》（book 94 para 287）——CST 三藏的坐标在各服务器上相同。
 * 容错打分的各档、译名可见性、layer 过滤由 api-v13 的 TipitakaTitleV3Test 覆盖。
 */

test('GET /v3/tipitaka-titles?q=（不带变音符号、拼错）→ 200，找到大般涅槃经', function () {
    $res = request(fn () => api(TipitakaTitlesApi::class)
        ->getApiV3TipitakaTitlesWithHttpInfo(1, 5, 'mahaparinibanasuta', null, null, 'mula'));
    assert_status(200, $res, 'tipitaka-titles q');
    $top = $res['body']['data'][0] ?? null;
    if (! $top) {
        fail('tipitaka-titles：拼错的经名应能找到');
    }
    assert_eq(94, $top['book'] ?? null, 'book');
    assert_eq(287, $top['para'] ?? null, 'para');
    assert_eq('mula', $top['layer'] ?? null, 'layer');
    foreach (['title', 'translations', 'paragraphs', 'chars', 'path', 'score'] as $key) {
        assert_has_key($key, $top, 'tipitaka-titles 行');
    }
});

test('GET /v3/tipitaka-titles?book=&para= → 200，按经文顺序列出这一章下面的标题', function () {
    $res = raw('GET', '/v3/tipitaka-titles', ['book' => 94, 'para' => 287, 'per_page' => 1000]);
    assert_status(200, $res, 'tipitaka-titles book');
    $paras = array_column($res['body']['data'] ?? [], 'para');
    assert_eq(287, $paras[0] ?? null, '第一条是这一章自己');
    $sorted = $paras;
    sort($sorted);
    assert_eq($sorted, $paras, '按段号升序');
    if (! in_array(585, $paras, true)) {
        fail('tipitaka-titles：应包含小节 94:585（Tathāgatapacchimavācā）');
    }
});

test('GET /v3/tipitaka-titles（q 与 book 都不给）→ 422', function () {
    $res = raw('GET', '/v3/tipitaka-titles');
    assert_status(422, $res, '无条件');
});
