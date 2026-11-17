<?php

use \WikipaliApi\Api\ChannelsApi;
use \WikipaliApi\Api\StudiosApi;
use \WikipaliApi\Model\PostApiV3ChannelsChannelTermsRequest;
use \WikipaliApi\Model\PostApiV3StudiosStudioChannelsRequest;
use GuzzleHttp\Client;

/*
 * 术语导入 / 导出（xlsx，取代 v2 terms-export / terms-import）：
 * 导出 = 列表接口带 Accept: xlsx；导入 = POST /v3/channels/{c}/terms/imports（multipart，部分成功）。
 * 用临时 channel（e2e-tmp-exchange-*）：建一条术语 → 导出 → 原样导入（全部「未变」）→ 删 channel。
 * 逐行规则（etag、word 不可改、studio 的 channel_id / language）由 api-v13 的 TermExchangeV3Test 覆盖。
 */

const TERM_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

test('POST /v3/channels/{channel}/terms/imports（无 token）→ 401', function () {
    $res = raw('POST', '/v3/channels/'.fixtures()['channel'].'/terms/imports', null, []);
    assert_status(401, $res, 'imports 无 token');
});

test('术语导出 → 原样导入一轮：xlsx 200 → 导入 200（未变 1）→ 坏文件 422', function () {
    $token = required_token();
    $me = raw('GET', '/v3/me', null, null, $token);
    $body = new PostApiV3StudiosStudioChannelsRequest();
    $body->setName('e2e-tmp-exchange-'.time());
    $body->setType('translation');
    $body->setLang('zh-Hans');
    $channel = request(fn () => api(StudiosApi::class, $token)->postApiV3StudiosStudioChannelsWithHttpInfo($me['body']['data']['id'], $body));
    assert_status(201, $channel, '建临时 channel');
    $channelId = $channel['body']['data']['id'];

    $term = new PostApiV3ChannelsChannelTermsRequest();
    $term->setWord('apiexchange');
    $term->setMeaning('导入导出测试');
    $created = request(fn () => api(ChannelsApi::class, $token)->postApiV3ChannelsChannelTermsWithHttpInfo($channelId, $term));
    assert_status(201, $created, '建术语');

    // 导出：生成客户端只认 JSON，这里直接拿二进制
    $http = new Client(['timeout' => config()['timeout'], 'http_errors' => false]);
    $export = $http->get(config()['base_url']."/v3/channels/{$channelId}/terms", [
        'headers' => ['Accept' => TERM_XLSX, 'Authorization' => "Bearer {$token}"],
    ]);
    assert_eq(200, $export->getStatusCode(), '导出状态码');
    assert_eq(TERM_XLSX, $export->getHeaderLine('Content-Type'), '导出的 Content-Type');
    $content = (string) $export->getBody();
    assert_eq('PK', substr($content, 0, 2), '导出的是 xlsx（zip）');

    $path = sys_get_temp_dir().'/api-test-terms-'.time().'.xlsx';
    file_put_contents($path, $content);
    $import = request(fn () => api(ChannelsApi::class, $token)->postApiV3ChannelsChannelTermsImportsWithHttpInfo($channelId, new \SplFileObject($path)));
    assert_status(200, $import, '原样导入');
    assert_eq(['created' => 0, 'updated' => 0, 'unchanged' => 1], array_intersect_key($import['body']['data'], ['created' => 0, 'updated' => 0, 'unchanged' => 0]), '原样导入全部未变');
    assert_eq([], $import['body']['data']['errors'], '没有跳过的行');

    file_put_contents($path, 'not a spreadsheet');
    $bad = request(fn () => api(ChannelsApi::class, $token)->postApiV3ChannelsChannelTermsImportsWithHttpInfo($channelId, new \SplFileObject($path)));
    assert_status(422, $bad, '坏文件');
    unlink($path);

    // channel 里有术语不能删：先删术语
    assert_status(200, raw('DELETE', '/v3/terms/'.$created['body']['data']['id'], null, null, $token), '删术语');
    assert_status(200, request(fn () => api(ChannelsApi::class, $token)->deleteApiV3ChannelsChannelWithHttpInfo($channelId)), '删临时 channel');
});
