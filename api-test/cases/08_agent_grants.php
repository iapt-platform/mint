<?php

use \WikipaliApi\Api\AgentGrantsApi;
use \WikipaliApi\Model\PostApiV3AgentGrantsRequest;

/*
 * agent 协作：人类签发写入授权（POST /v3/agent-grants），模型凭模型 token + X-Agent-Grant 代写。
 *
 * 完整代写链路需要一个归测试账号所有的 AI 模型：在 config.local.php 的
 * fixtures.agent_model 填它的 uid，没填则 SKIP。模型 token 目前只有 v2 端点能取
 * （GET /v2/ai-model-token/{uid}），这里只把它当准备步骤用。
 * 权限矩阵（别的 channel / book、撤销、伪造、v2 token 冒充）由 api-v13 的 AgentGrantV3Test 覆盖。
 */

function agent_grant_body(string $model, string $channel): PostApiV3AgentGrantsRequest
{
    $body = new PostApiV3AgentGrantsRequest();
    $body->setAiModel($model);
    $body->setTargetType('channel');
    $body->setTargetId($channel);

    return $body;
}

test('POST /v3/agent-grants（无 token）→ 401', function () {
    $res = request(fn () => api(AgentGrantsApi::class)->postApiV3AgentGrantsWithHttpInfo(
        agent_grant_body('00000000-0000-4000-8000-000000000000', fixtures()['channel'])
    ));
    assert_status(401, $res, 'agent-grants 无 token');
    assert_problem($res, 'agent-grants 无 token');
});

test('POST /v3/agent-grants（模型不存在）→ 422 errors.ai_model', function () {
    $res = request(fn () => api(AgentGrantsApi::class, required_token())->postApiV3AgentGrantsWithHttpInfo(
        agent_grant_body('00000000-0000-4000-8000-000000000000', fixtures()['channel'])
    ));
    assert_field_error('ai_model', $res, 'agent-grants 模型不存在');
});

test('POST /v3/agent-grants（target_type 不是 channel）→ 422', function () {
    $res = raw('POST', '/v3/agent-grants', null, [
        'ai_model' => '00000000-0000-4000-8000-000000000000', 'target_type' => 'article', 'target_id' => fixtures()['channel'],
    ], required_token());
    assert_field_error('target_type', $res, 'agent-grants target_type');
});

test('agent 代写一轮：签授权 → 模型带授权建批注（editor=人、co_editor=模型）→ 不带授权 403 → 人删掉', function () {
    $model = fixtures()['agent_model'] ?? null;
    if (! $model) {
        skip('config.local.php 未配置 fixtures.agent_model（归测试账号所有的 AI 模型 uid）');
    }
    $token = required_token();
    $sentence = fixtures()['discussion_sentence'];
    $channel = fixtures()['discussion_channel'];

    $grant = request(fn () => api(AgentGrantsApi::class, $token)->postApiV3AgentGrantsWithHttpInfo(agent_grant_body($model, $channel)));
    assert_status(201, $grant, '签授权');
    $grantToken = $grant['body']['data']['token'] ?? null;

    // 准备：取模型 token（只有 v2 端点）
    $modelToken = raw('GET', "/v2/ai-model-token/{$model}", null, null, $token)['body']['data']['token'] ?? null;
    if (! $modelToken) {
        fail('取不到模型 token：fixtures.agent_model 是否归测试账号所有？');
    }

    $body = ['target_type' => 'sentence', 'target_id' => $sentence, 'type' => 'note', 'content' => 'api-test 代写的批注'];
    $denied = raw('POST', '/v3/discussions', null, $body, $modelToken);
    assert_status(403, $denied, '模型不带授权');

    $created = raw('POST', '/v3/discussions', null, $body, $modelToken, ['X-Agent-Grant' => $grantToken]);
    assert_status(201, $created, '模型带授权');
    $id = $created['body']['data']['id'] ?? null;
    try {
        assert_eq($model, $created['body']['data']['co_editor']['id'] ?? null, 'co_editor 是模型');
        if (($created['body']['data']['editor']['id'] ?? null) === $model) {
            fail('editor 应是授权人，不是模型');
        }
    } finally {
        $del = raw('DELETE', "/v3/discussions/{$id}", null, null, $token);
    }
    assert_status(200, $del, '人删掉模型写的');
});
