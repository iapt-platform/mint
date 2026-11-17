<?php

use \WikipaliApi\Api\ChannelsApi;
use \WikipaliApi\Api\StudiosApi;
use \WikipaliApi\Api\TermsApi;
use \WikipaliApi\Model\PostApiV3ChannelsChannelTermsRequest;
use \WikipaliApi\Model\PostApiV3StudiosStudioChannelsRequest;

/*
 * v3 术语：/v3/terms、/v3/channels/{c}/terms、/v3/studios/{s}/terms。
 * 写操作用临时 channel（e2e-tmp-terms-*）：建 → 读（ETag）→ 改（If-Match，412 / 428）→ 删（软删），最后删掉 channel。
 * 权限矩阵（团队角色、AI 代写）、分类抽取、N+1 由 api-v13 的 TermV3Test 覆盖。
 */

test('GET /v3/terms?word= → 200；缺 word → 422', function () {
    $res = request(fn () => api(TermsApi::class)->getApiV3TermsWithHttpInfo('dhamma', 5));
    assert_status(200, $res, 'terms lookup');
    assert_has_key('meta', $res['body'], 'terms lookup');

    $bad = raw('GET', '/v3/terms');
    assert_status(422, $bad, 'terms 缺 word');
    assert_field_error('word', $bad, 'terms 缺 word');
});

test('GET /v3/channels/{channel}/terms → 200；GET /v3/terms/{不存在} → 404', function () {
    $res = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsChannelTermsWithHttpInfo(fixtures()['channel'], 5));
    assert_status(200, $res, 'channel terms');
    assert_has_key('meta', $res['body'], 'channel terms');

    assert_status(404, raw('GET', '/v3/terms/11111111-1111-4111-8111-111111111111'), '术语不存在');
});

test('写术语（无 token）→ 401', function () {
    $res = raw('POST', '/v3/channels/'.fixtures()['channel'].'/terms', null, ['word' => 'x', 'meaning' => 'y']);
    assert_status(401, $res, 'terms 无 token');
    assert_problem($res, 'terms 无 token');
});

test('terms 一轮：建 201 → 读带 ETag → 不带 If-Match 428 → 旧 etag 412 → 改 200 → 删 200（软删）→ 再看 404', function () {
    $token = required_token();
    $me = raw('GET', '/v3/me', null, null, $token);
    assert_status(200, $me, 'me');
    $body = new PostApiV3StudiosStudioChannelsRequest();
    $body->setName('e2e-tmp-terms-'.time());
    $body->setType('translation');
    $body->setLang('zh-Hans');
    $channel = request(fn () => api(StudiosApi::class, $token)->postApiV3StudiosStudioChannelsWithHttpInfo($me['body']['data']['id'], $body));
    assert_status(201, $channel, '建临时 channel');
    $channelId = $channel['body']['data']['id'];

    $termBody = new PostApiV3ChannelsChannelTermsRequest();
    $termBody->setWord('apitest');
    $termBody->setMeaning('接口测试');
    $termBody->setNote("第一段。\n\n{{category|api-test}}");
    $termBody->setQuality('draft');
    $create = request(fn () => api(ChannelsApi::class, $token)->postApiV3ChannelsChannelTermsWithHttpInfo($channelId, $termBody));
    assert_status(201, $create, '建术语');
    $term = $create['body']['data'];
    assert_eq('markdown-e2', $term['content_type'] ?? null, '新建默认 markdown-e2');
    assert_eq(['api-test'], $term['categories'] ?? null, '分类从正文抽出');

    $dup = raw('POST', "/v3/channels/{$channelId}/terms", null, ['word' => 'apitest', 'meaning' => '重复'], $token);
    assert_status(409, $dup, '同一 word@tag 重复');

    $show = raw('GET', "/v3/terms/{$term['id']}", null, null, $token);
    assert_status(200, $show, '读术语');
    assert_eq($term['etag'], $show['headers']['etag'][0] ?? null, 'ETag 头与 etag 字段一致');
    assert_has_key('body', $show['body']['data'], '单条带渲染后的正文');

    assert_status(428, raw('PATCH', "/v3/terms/{$term['id']}", null, ['meaning' => '改'], $token), '不带 If-Match');
    assert_status(412, raw('PATCH', "/v3/terms/{$term['id']}", null, ['meaning' => '改'], $token, ['If-Match' => '"0.0"']), '旧 etag');
    $word = raw('PATCH', "/v3/terms/{$term['id']}", null, ['word' => 'other'], $token, ['If-Match' => $term['etag']]);
    assert_status(422, $word, '词头不能改');
    assert_field_error('word', $word, '词头不能改');

    $update = raw('PATCH', "/v3/terms/{$term['id']}", null, ['meaning' => '改过的'], $token, ['If-Match' => $term['etag']]);
    assert_status(200, $update, '改术语');
    assert_eq('改过的', $update['body']['data']['meaning'] ?? null, '改后的释义');

    $delete = request(fn () => api(TermsApi::class, $token)->deleteApiV3TermsTermWithHttpInfo($term['id']));
    assert_status(200, $delete, '删术语');
    assert_status(404, raw('GET', "/v3/terms/{$term['id']}", null, null, $token), '删后再看');

    assert_status(200, request(fn () => api(ChannelsApi::class, $token)->deleteApiV3ChannelsChannelWithHttpInfo($channelId)), '删临时 channel');
});
