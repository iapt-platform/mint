<?php

use \WikipaliApi\Api\TipitakaExportsApi;
use \WikipaliApi\Model\PostApiV3TipitakaExportsRequest;

/*
| 三藏下载 /v3/tipitaka-exports：发起 → 轮询 → 签名链接下载。
| 发起是公开的（公开 channel 不用登录）；生成要服务器上的 worker（mq:export.tipitaka）在跑，
| 60 秒内没做完算失败。生成的文件留在 Redis 里 7 天，同一份再跑直接命中，不累积。
*/

test('POST /v3/tipitaka-exports（作品 markdown）→ 200/202，轮询到 ready，签名链接能下载、支持 Range', function () {
    $body = new PostApiV3TipitakaExportsRequest([
        'channel' => fixtures()['export_channel'],
        'work' => fixtures()['export_work'],
        'format' => 'markdown',
    ]);
    $res = request(fn () => api(TipitakaExportsApi::class)->postApiV3TipitakaExportsWithHttpInfo($body));
    if (! in_array($res['status'], [200, 202], true)) {
        assert_status(202, $res, 'tipitaka-exports POST');
    }
    $data = $res['body']['data'] ?? [];
    assert_has_key('id', $data, 'tipitaka-exports.data');
    assert_eq('markdown', $data['format'] ?? null, 'tipitaka-exports.data.format');

    $deadline = time() + 60;
    while (($data['status'] ?? null) !== 'ready') {
        if (($data['status'] ?? null) === 'failed') {
            fail('tipitaka-exports 生成失败：' . ($data['message'] ?? ''));
        }
        if (time() > $deadline) {
            fail('tipitaka-exports 60 秒内没有生成好（worker mq:export.tipitaka 在跑吗？）status=' . ($data['status'] ?? '?'));
        }
        sleep(2);
        $poll = request(fn () => api(TipitakaExportsApi::class)->getApiV3TipitakaExportsExportWithHttpInfo($data['id']));
        assert_status(200, $poll, 'tipitaka-exports GET');
        $data = $poll['body']['data'];
    }
    if (! str_starts_with((string) ($data['download_url'] ?? ''), '/api/v3/tipitaka-exports/')) {
        fail('tipitaka-exports.download_url 应是相对地址，实际 ' . ($data['download_url'] ?? 'null'));
    }

    // download_url 已带 /api 前缀，base_url 也带，去掉一层
    $path = substr($data['download_url'], strlen('/api'));
    $file = raw('GET', $path);
    assert_status(200, $file, 'tipitaka-exports file');
    assert_eq('application/zip', $file['headers']['content-type'][0] ?? null, 'tipitaka-exports file content-type');
    assert_eq((string) $data['size'], $file['headers']['content-length'][0] ?? null, 'tipitaka-exports file content-length');

    $part = raw('GET', $path, null, null, null, ['Range' => 'bytes=0-1']);
    assert_status(206, $part, 'tipitaka-exports file Range');
    assert_eq('bytes 0-1/' . $data['size'], $part['headers']['content-range'][0] ?? null, 'tipitaka-exports Content-Range');
});

test('GET /v3/tipitaka-exports/{id}/file 没有签名 → 403', function () {
    $res = raw('GET', '/v3/tipitaka-exports/' . str_repeat('0', 40) . '/file');
    assert_status(403, $res, 'tipitaka-exports file 无签名');
});

test('GET /v3/tipitaka-exports/{不存在} → 404', function () {
    $res = raw('GET', '/v3/tipitaka-exports/' . str_repeat('0', 40));
    assert_status(404, $res, 'tipitaka-exports 不存在');
    assert_problem($res, 'tipitaka-exports 不存在');
});

test('POST /v3/tipitaka-exports 缺 format → 422', function () {
    $res = raw('POST', '/v3/tipitaka-exports', null, ['channel' => fixtures()['export_channel']]);
    assert_status(422, $res, 'tipitaka-exports 缺 format');
    assert_field_error('format', $res, 'tipitaka-exports 缺 format');
});

test('POST /v3/tipitaka-exports 离线包不按作品 → 422', function () {
    $res = raw('POST', '/v3/tipitaka-exports', null, ['channel' => fixtures()['export_channel'], 'format' => 'offline']);
    assert_status(422, $res, 'tipitaka-exports offline 无 work');
    assert_field_error('format', $res, 'tipitaka-exports offline 无 work');
});

test('POST /v3/tipitaka-exports 没收录的作品 → 404', function () {
    $res = raw('POST', '/v3/tipitaka-exports', null, ['channel' => fixtures()['export_channel'], 'work' => 999999, 'format' => 'html']);
    assert_status(404, $res, 'tipitaka-exports 未收录作品');
    assert_problem($res, 'tipitaka-exports 未收录作品');
});
