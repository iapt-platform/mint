<?php

use \WikipaliApi\Api\ChannelsApi;
use \WikipaliApi\Api\StudiosApi;
use \WikipaliApi\Model\PatchApiV3ChannelsChannelLinksRequest;
use \WikipaliApi\Model\PostApiV3StudiosStudioChannelsRequest;

/*
 * channel 之间的关系设置：GET / PATCH /v3/channels/{channel}/links
 *
 * 写操作用两个临时 channel（e2e-tmp-links-*），测完清空关系并删掉（软删）。
 * 权限矩阵（团队角色、AI 模型）、目标可见性、N+1 由 api-v13 的 ChannelLinkV3Test 覆盖。
 */

/** 在本人名下建一个临时 channel，返回 uid */
function links_tmp_channel(string $token, string $suffix): string
{
    $me = raw('GET', '/v3/me', null, null, $token);
    assert_status(200, $me, 'me');
    $body = new PostApiV3StudiosStudioChannelsRequest();
    $body->setName("e2e-tmp-links-{$suffix}-".time());
    $body->setType('translation');
    $body->setLang('en');
    $res = request(fn () => api(StudiosApi::class, $token)->postApiV3StudiosStudioChannelsWithHttpInfo($me['body']['data']['id'], $body));
    assert_status(201, $res, "建临时 channel {$suffix}");

    return $res['body']['data']['id'];
}

test('GET /v3/channels/{channel}/links → 200，四种关系都在', function () {
    $res = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsChannelLinksWithHttpInfo(fixtures()['channel']));
    assert_status(200, $res, 'links');
    foreach (['channel_id', 'derived_from', 'wbw', 'fallback', 'term_source', 'can_update'] as $key) {
        assert_has_key($key, $res['body']['data'], 'links');
    }
    assert_eq(false, $res['body']['data']['can_update'], '匿名不能改');
});

test('GET /v3/channels/{不存在}/links → 404', function () {
    assert_status(404, raw('GET', '/v3/channels/11111111-1111-4111-8111-111111111111/links'), '不存在的 channel');
});

test('PATCH /v3/channels/{channel}/links（无 token）→ 401', function () {
    $res = raw('PATCH', '/v3/channels/'.fixtures()['channel'].'/links', null, ['fallback' => []]);
    assert_status(401, $res, 'links 无 token');
    assert_problem($res, 'links 无 token');
});

test('channel links 一轮：设置 200 → 读回 → 指向自己 422 → 清空 200', function () {
    $token = required_token();
    $a = links_tmp_channel($token, 'a');
    $b = links_tmp_channel($token, 'b');

    $body = new PatchApiV3ChannelsChannelLinksRequest();
    $body->setDerivedFrom($b);
    $body->setFallback([$b]);
    $body->setTermSource([$b]);
    $set = request(fn () => api(ChannelsApi::class, $token)->patchApiV3ChannelsChannelLinksWithHttpInfo($a, $body));
    assert_status(200, $set, '设置关系');
    assert_eq($b, $set['body']['data']['derived_from']['id'] ?? null, 'derived_from');
    assert_eq(true, $set['body']['data']['can_update'], '拥有者能改');

    $read = request(fn () => api(ChannelsApi::class, $token)->getApiV3ChannelsChannelLinksWithHttpInfo($a));
    assert_status(200, $read, '读回');
    assert_eq([$b], array_column($read['body']['data']['fallback'], 'id'), 'fallback 读回');

    $self = raw('PATCH', "/v3/channels/{$a}/links", null, ['derived_from' => $a], $token);
    assert_status(422, $self, '指向自己');
    assert_field_error('derived_from', $self, '指向自己');

    $bad = raw('PATCH', "/v3/channels/{$a}/links", null, ['wbw' => $b], $token);
    assert_status(422, $bad, '逐词目标不是 wbw');
    assert_field_error('wbw', $bad, '逐词目标不是 wbw');

    $clear = raw('PATCH', "/v3/channels/{$a}/links", null, ['derived_from' => null, 'fallback' => [], 'term_source' => []], $token);
    assert_status(200, $clear, '清空');
    assert_eq(null, $clear['body']['data']['derived_from'], '清空后 derived_from');

    foreach ([$a, $b] as $uid) {
        assert_status(200, request(fn () => api(ChannelsApi::class, $token)->deleteApiV3ChannelsChannelWithHttpInfo($uid)), '删临时 channel');
    }
});
