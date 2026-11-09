<?php

use \WikipaliApi\Api\TipitakaChapterChannelsApi;

/*
 * 一章有哪些 channel 及完成度：GET /v3/tipitaka-chapter-channels/{book}/{para}
 *
 * 只读、无写操作。章节坐标从 fixture channel 自己的进度里取（各服务器数据不同），
 * 再核对这一章的列表里有它。fixture channel 是公开的，匿名就看得到。
 * 可见性矩阵（私有 / 共享 / 群组）、排序、N+1 由 api-v13 的 TipitakaChapterChannelV3Test 覆盖。
 */

/** fixture channel 有进度的某一章的 [book, para] */
function chapter_channel_coord(): array
{
    $res = raw('GET', '/v3/progress', ['channels' => fixtures()['channel'], 'per_page' => 1]);
    assert_status(200, $res, '取 fixture channel 的章节进度');
    $row = $res['body']['data'][0] ?? null;
    if (! $row) {
        skip('fixture channel 在这台服务器上没有章节进度');
    }

    return [(int) $row['book'], (int) $row['para']];
}

test('GET /v3/tipitaka-chapter-channels/{book}/{para} → 200，含 fixture channel', function () {
    [$book, $para] = chapter_channel_coord();
    $res = request(fn () => api(TipitakaChapterChannelsApi::class)
        ->getApiV3TipitakaChapterChannelsBookParaWithHttpInfo($book, $para, 200));
    assert_status(200, $res, 'tipitaka-chapter-channels');
    assert_has_key('data', $res['body'], 'tipitaka-chapter-channels');
    assert_has_key('meta', $res['body'], 'tipitaka-chapter-channels');

    $hit = null;
    foreach ($res['body']['data'] as $row) {
        if (($row['channel']['id'] ?? null) === fixtures()['channel']) {
            $hit = $row;
        }
    }
    if (! $hit) {
        fail('tipitaka-chapter-channels：应包含 fixture channel');
    }
    assert_eq($book, $hit['book'] ?? null, 'book');
    assert_eq($para, $hit['para'] ?? null, 'para');
    foreach (['id', 'progress', 'views', 'updated_at', 'studio'] as $key) {
        assert_has_key($key, $hit, 'tipitaka-chapter-channels 行');
    }

    // 按完成度降序
    $progress = array_column($res['body']['data'], 'progress');
    $sorted = $progress;
    rsort($sorted);
    assert_eq($sorted, $progress, '按 progress 降序');
});

test('GET /v3/tipitaka-chapter-channels/{book}/{para}（没有进度的章节）→ 200 空列表', function () {
    $res = raw('GET', '/v3/tipitaka-chapter-channels/1/999999');
    assert_status(200, $res, '空章节');
    assert_eq([], $res['body']['data'] ?? null, '空章节 data');
});

test('GET /v3/tipitaka-chapter-channels/{book}/{para}?per_page=500 → 422', function () {
    $res = raw('GET', '/v3/tipitaka-chapter-channels/1/2', ['per_page' => 500]);
    assert_status(422, $res, 'per_page 超限');
    assert_field_error('per_page', $res, 'per_page 超限');
});

test('GET /v3/tipitaka-chapter-channels/{非数字}/{para} → 404', function () {
    assert_status(404, raw('GET', '/v3/tipitaka-chapter-channels/abc/2'), '非数字 book');
});
