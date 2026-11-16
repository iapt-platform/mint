<?php

use \WikipaliApi\Api\ChannelsApi;
use \WikipaliApi\Api\StudiosApi;
use \WikipaliApi\Api\TipitakaChannelsApi;
use \WikipaliApi\Api\TipitakaParagraphChannelsApi;
use \WikipaliApi\Model\PatchApiV3ChannelsChannelRequest;
use \WikipaliApi\Model\PostApiV3ChannelsBatchProgressRequest;
use \WikipaliApi\Model\PostApiV3StudiosStudioChannelsRequest;

/*
 * v3 channel：公开目录、详情、我能写的、studio 列表、建 → 改 → 删、阅读领域的两个查询、batch-progress。
 * 「建 → 改 → 删」用临时 channel（e2e-tmp-*），删是软删除。
 */

/** 测试账号自己的 uuid */
function my_uid(string $token): string
{
    $me = raw('GET', '/v3/me', null, null, $token);
    assert_status(200, $me, 'me');

    return $me['body']['data']['id'];
}

/* ---- 公开读 ---- */

test('GET /v3/channels → 200，只有公开的', function () {
    $res = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsWithHttpInfo());
    assert_status(200, $res, 'channels');
    assert_has_key('meta', $res['body'], 'channels');
    foreach ($res['body']['data'] as $channel) {
        assert_eq(30, $channel['status'], 'channels 只列公开的');
        assert_has_key('abilities', $channel, 'channels.data[]');
    }
});

test('GET /v3/channels?system=true → 200，都是系统 channel', function () {
    $res = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsWithHttpInfo(system: true, per_page: 50));
    assert_status(200, $res, 'channels system');
    foreach ($res['body']['data'] as $channel) {
        assert_eq(true, $channel['is_system'], 'system=true 只列系统 channel');
    }
});

test('GET /v3/channels?para=（缺 book）→ 422', function () {
    $res = raw('GET', '/v3/channels', ['para' => 1]);
    assert_status(422, $res, 'channels 缺 book');
    assert_field_error('book', $res, 'channels 缺 book');
});

test('GET /v3/channels/{channel} → 200；不存在 → 404', function () {
    $res = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsChannelWithHttpInfo(fixtures()['channel']));
    assert_status(200, $res, 'channel 详情');
    assert_has_key('owner', $res['body']['data'], 'channel 详情');

    $missing = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsChannelWithHttpInfo('11111111-1111-4111-8111-111111111111'));
    assert_status(404, $missing, 'channel 不存在');
});

test('GET /v3/tipitaka-channels?lang=zh → 200，带 chapter_count', function () {
    $res = request(fn () => api(TipitakaChannelsApi::class)->getApiV3TipitakaChannelsWithHttpInfo(lang: 'zh', type: 'translation'));
    assert_status(200, $res, 'tipitaka-channels');
    $first = $res['body']['data'][0] ?? null;
    if ($first) {
        assert_has_key('chapter_count', $first, 'tipitaka-channels.data[0]');
    }
});

test('GET /v3/tipitaka-paragraph-channels/{book}/{para} → 200', function () {
    $res = request(fn () => api(TipitakaParagraphChannelsApi::class)->getApiV3TipitakaParagraphChannelsBookParaWithHttpInfo(fixtures()['book'], fixtures()['para']));
    assert_status(200, $res, 'tipitaka-paragraph-channels');
    assert_has_key('meta', $res['body'], 'tipitaka-paragraph-channels');
});

test('POST /v3/channels/batch-progress → 200；格式不对 → 422', function () {
    $body = new PostApiV3ChannelsBatchProgressRequest();
    $body->setSentences(['1-2-1-10']);
    $res = request(fn () => api(ChannelsApi::class)->postApiV3ChannelsBatchProgressWithHttpInfo($body));
    assert_status(200, $res, 'batch-progress');

    $bad = raw('POST', '/v3/channels/batch-progress', null, ['sentences' => ['1-2']]);
    assert_status(422, $bad, 'batch-progress 格式不对');
});

/* ---- 要登录 ---- */

test('GET /v3/channels/editable（无 token）→ 401；带 token → 200', function () {
    $anon = request(fn () => api(ChannelsApi::class)->getApiV3ChannelsEditableWithHttpInfo());
    assert_status(401, $anon, 'editable 无 token');
    assert_problem($anon, 'editable 无 token');

    $res = request(fn () => api(ChannelsApi::class, required_token())->getApiV3ChannelsEditableWithHttpInfo(per_page: 5));
    assert_status(200, $res, 'editable');
    foreach ($res['body']['data'] as $channel) {
        assert_eq(true, $channel['abilities']['write'], 'editable 里的都能写');
    }
});

test('GET /v3/studios/{studio}/channels：本人 200，无 token 401', function () {
    $token = required_token();
    $me = my_uid($token);
    $res = request(fn () => api(StudiosApi::class, $token)->getApiV3StudiosStudioChannelsWithHttpInfo($me, relation: 'own', per_page: 5));
    assert_status(200, $res, 'studio channels');
    assert_has_key('meta', $res['body'], 'studio channels');

    $anon = request(fn () => api(StudiosApi::class)->getApiV3StudiosStudioChannelsWithHttpInfo($me));
    assert_status(401, $anon, 'studio channels 无 token');
});

test('POST /v3/studios/{studio}/channels（缺 name）→ 422', function () {
    $token = required_token();
    $res = raw('POST', '/v3/studios/'.my_uid($token).'/channels', null, ['type' => 'translation', 'lang' => 'zh'], $token);
    assert_status(422, $res, 'channels 缺 name');
    assert_field_error('name', $res, 'channels 缺 name');
});

test('channels 一轮：建 201 → 改 200（只改传了的）→ 删 200（软删）→ 再看 404', function () {
    $token = required_token();
    $me = my_uid($token);

    $body = new PostApiV3StudiosStudioChannelsRequest();
    $body->setName('e2e-tmp-'.time());
    $body->setType('translation');
    $body->setLang('zh-Hans');
    $body->setSummary('api-test');
    $create = request(fn () => api(StudiosApi::class, $token)->postApiV3StudiosStudioChannelsWithHttpInfo($me, $body));
    assert_status(201, $create, '建 channel');
    assert_eq(10, $create['body']['data']['status'] ?? null, '新建默认私有');
    $channel = $create['body']['data']['id'];

    $patch = new PatchApiV3ChannelsChannelRequest();
    $patch->setStatus(30);
    $update = request(fn () => api(ChannelsApi::class, $token)->patchApiV3ChannelsChannelWithHttpInfo($channel, $patch));
    assert_status(200, $update, '改 channel');
    assert_eq(30, $update['body']['data']['status'] ?? null, '改后的状态');
    assert_eq('api-test', $update['body']['data']['summary'] ?? null, '没传的字段不变');

    $delete = request(fn () => api(ChannelsApi::class, $token)->deleteApiV3ChannelsChannelWithHttpInfo($channel));
    assert_status(200, $delete, '删 channel');

    $gone = request(fn () => api(ChannelsApi::class, $token)->getApiV3ChannelsChannelWithHttpInfo($channel));
    assert_status(404, $gone, '删后再看');
});

test('tipitaka-reading 私有 channel：无 token 403，拥有者 200', function () {
    $token = required_token();
    $body = new PostApiV3StudiosStudioChannelsRequest();
    $body->setName('e2e-tmp-private-'.time());
    $body->setType('translation');
    $body->setLang('zh-Hans');
    $create = request(fn () => api(StudiosApi::class, $token)->postApiV3StudiosStudioChannelsWithHttpInfo(my_uid($token), $body));
    assert_status(201, $create, '建私有 channel');
    $channel = $create['body']['data']['id'];

    $anon = raw('GET', "/v3/tipitaka-reading/{$channel}");
    assert_status(403, $anon, '无 token 读私有正文');
    assert_problem($anon, '无 token 读私有正文');
    $mine = raw('GET', "/v3/tipitaka-reading/{$channel}", null, null, $token);
    assert_status(200, $mine, '拥有者读私有正文');

    $chapter = raw('GET', "/v3/search/tipitaka_chapter_1-1_{$channel}");
    assert_status(403, $chapter, '无 token 取私有 channel 的整章文档');

    request(fn () => api(ChannelsApi::class, $token)->deleteApiV3ChannelsChannelWithHttpInfo($channel));
});
