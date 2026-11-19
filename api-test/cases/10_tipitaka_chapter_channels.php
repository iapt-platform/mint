<?php

use \WikipaliApi\Api\TipitakaChapterChannelsApi;

/*
 * 一章有哪些 channel 及完成度：GET /v3/tipitaka-chapter-channels/{book}/{para}
 *
 * 只读、无写操作。章节坐标取 fixtures.php 的 chapter_channels_book / chapter_channels_para
 * （与 server 无关），核对这一章能匿名列出版本、行的形状与排序对。
 * 可见性矩阵（私有 / 共享 / 群组）、排序、N+1 由 api-v13 的 TipitakaChapterChannelV3Test 覆盖。
 */

/** 有若干译文版本的某一章的 [book, para] */
function chapter_channel_coord(): array
{
    return [(int) fixtures()['chapter_channels_book'], (int) fixtures()['chapter_channels_para']];
}

test('GET /v3/tipitaka-chapter-channels/{book}/{para} → 200，列出版本', function () {
    [$book, $para] = chapter_channel_coord();
    $res = request(fn () => api(TipitakaChapterChannelsApi::class)
        ->getApiV3TipitakaChapterChannelsBookParaWithHttpInfo($book, $para, 200));
    assert_status(200, $res, 'tipitaka-chapter-channels');
    assert_has_key('data', $res['body'], 'tipitaka-chapter-channels');
    assert_has_key('meta', $res['body'], 'tipitaka-chapter-channels');

    if ($res['body']['data'] === []) {
        fail('tipitaka-chapter-channels：应返回至少一个版本');
    }
    foreach ($res['body']['data'] as $row) {
        assert_eq($book, $row['book'] ?? null, 'book');
        assert_eq($para, $row['para'] ?? null, 'para');
        foreach (['id', 'progress', 'views', 'updated_at', 'channel', 'studio'] as $key) {
            assert_has_key($key, $row, 'tipitaka-chapter-channels 行');
        }
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
