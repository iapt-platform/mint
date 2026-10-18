<?php

use \WikipaliApi\Api\DiscussionsApi;
use \WikipaliApi\Model\PatchApiV3DiscussionsDiscussionRequest;
use \WikipaliApi\Model\PostApiV3DiscussionsBatchCountRequest;
use \WikipaliApi\Model\PostApiV3DiscussionsBatchCountRequestTargetsInner;
use \WikipaliApi\Model\PostApiV3DiscussionsDiscussionRepliesRequest;
use \WikipaliApi\Model\PostApiV3DiscussionsRequest;

/*
 * 讨论（评论线程）：话题列表 / 统计 / 单条 / 回复 / 新建 / PATCH / 软删除。
 *
 * ⚠️ 写用例在所有服务器上真实执行。挂在 fixtures 的 discussion_sentence 上（测试账号
 * 自己的句子），带 notify=false 不发通知，最后删掉。删除是软删：行还在表里、标了
 * deleted_at，对外已不可见（再 GET 404）。
 * 权限矩阵（basic 角色、匿名可见性、谁能关闭）由 api-v13 的 DiscussionV3Test 覆盖。
 */

function discussion_target(): string
{
    return fixtures()['discussion_sentence'];
}

/* ---- 只读 ---- */

test('GET /v3/discussions?target_type=sentence&target_id=… → 200', function () {
    $res = request(fn () => api(DiscussionsApi::class)->getApiV3DiscussionsWithHttpInfo('sentence', discussion_target()));
    assert_status(200, $res, 'discussions');
    assert_has_key('data', $res['body'], 'discussions');
    assert_has_key('meta', $res['body'], 'discussions');
});

test('GET /v3/discussions（缺 target_id）→ 422', function () {
    $res = raw('GET', '/v3/discussions', ['target_type' => 'sentence']);
    assert_status(422, $res, 'discussions 缺 target_id');
    assert_field_error('target_id', $res, 'discussions 缺 target_id');
});

test('GET /v3/discussions（非法 target_type）→ 422', function () {
    $res = raw('GET', '/v3/discussions', ['target_type' => 'channel', 'target_id' => discussion_target()]);
    assert_status(422, $res, 'discussions 非法 target_type');
    assert_field_error('target_type', $res, 'discussions 非法 target_type');
});

test('GET /v3/discussions/summary → 200，含 status_counts 与 creatable_types', function () {
    $res = request(fn () => api(DiscussionsApi::class)->getApiV3DiscussionsSummaryWithHttpInfo('sentence', discussion_target()));
    assert_status(200, $res, 'summary');
    $data = $res['body']['data'] ?? null;
    assert_has_key('status_counts', $data, 'summary.data');
    assert_has_key('creatable_types', $data, 'summary.data');
    // 匿名：不能发起任何话题
    assert_eq([], $data['creatable_types'], '匿名 creatable_types');
});

test('GET /v3/discussions/{不存在} → 404', function () {
    $res = raw('GET', '/v3/discussions/00000000-0000-4000-8000-000000000000');
    assert_status(404, $res, 'discussions 不存在');
    assert_problem($res, 'discussions 不存在');
});

test('PUT /v3/discussions/{id} → 405（只有 PATCH）', function () {
    $res = raw('PUT', '/v3/discussions/00000000-0000-4000-8000-000000000000', null, ['title' => 'x'], required_token());
    assert_status(405, $res, 'PUT');
});

test('POST /v3/discussions/batch-count → 200，按请求顺序、没有讨论的给 0', function () {
    $sentence = new PostApiV3DiscussionsBatchCountRequestTargetsInner();
    $sentence->setType('sentence');
    $sentence->setId(discussion_target());
    $wbw = new PostApiV3DiscussionsBatchCountRequestTargetsInner();
    $wbw->setType('wbw');
    $wbw->setId('00000000-0000-4000-8000-000000000000');
    $body = new PostApiV3DiscussionsBatchCountRequest();
    $body->setTargets([$sentence, $wbw]);

    $res = request(fn () => api(DiscussionsApi::class)->postApiV3DiscussionsBatchCountWithHttpInfo($body));
    assert_status(200, $res, 'batch-count');
    assert_eq(discussion_target(), $res['body']['data'][0]['target_id'] ?? null, 'batch-count 顺序');
    assert_eq(0, $res['body']['data'][1]['total'] ?? null, 'batch-count 没有讨论给 0');
    assert_eq(0, $res['body']['data'][0]['mine'] ?? null, '匿名 mine 恒为 0');
});

test('POST /v3/discussions/batch-count（空 / 非法类型）→ 422', function () {
    assert_status(422, raw('POST', '/v3/discussions/batch-count', null, ['targets' => []]), 'batch-count 空');
    $bad = raw('POST', '/v3/discussions/batch-count', null, ['targets' => [['type' => 'article', 'id' => discussion_target()]]]);
    assert_field_error('targets.0.type', $bad, 'batch-count 非法类型');
});

/* ---- 无 token → 401 ---- */

test('POST /v3/discussions（无 token）→ 401', function () {
    $body = new PostApiV3DiscussionsRequest();
    $body->setTargetType('sentence');
    $body->setTargetId(discussion_target());
    $body->setType('discussion');
    $body->setTitle('api-test 无 token');
    $res = request(fn () => api(DiscussionsApi::class)->postApiV3DiscussionsWithHttpInfo($body));
    assert_status(401, $res, 'POST 无 token');
    assert_problem($res, 'POST 无 token');
});

/* ---- 带 token：一整轮 ---- */

test('discussions 一轮：建话题 → 回复 → 关闭后回复 409 → 重开 → 改类型 → 无 token 删 401 → 删回复 → 删话题（软删）→ 再查 404', function () {
    $token = required_token();
    $client = api(DiscussionsApi::class, $token);
    $stamp = 'api-test-' . date('Ymd-His');

    // 建话题：201，带 abilities，作者能改能删
    $body = new PostApiV3DiscussionsRequest();
    $body->setTargetType('sentence');
    $body->setTargetId(discussion_target());
    $body->setType('issue');
    $body->setSeverity('warning');
    $body->setTitle($stamp);
    $body->setContent('由 api-test 建立，测完即删');
    $body->setNotify(false);
    $create = request(fn () => $client->postApiV3DiscussionsWithHttpInfo($body));
    assert_status(201, $create, '建话题');
    $topic = $create['body']['data'] ?? [];
    $id = $topic['id'] ?? null;
    if (! $id) {
        fail('建话题：应返回 id');
    }

    try {
        assert_eq('warning', $topic['severity'] ?? null, '建话题 severity');
        assert_eq(true, $topic['abilities']['update'] ?? null, '作者 abilities.update');
        assert_eq(true, $topic['abilities']['delete'] ?? null, '作者 abilities.delete');

        // severity 与类型不匹配 → 422
        $bad = raw('POST', '/v3/discussions', null, [
            'target_type' => 'sentence', 'target_id' => discussion_target(),
            'type' => 'discussion', 'severity' => 'error', 'title' => $stamp, 'notify' => false,
        ], $token);
        assert_status(422, $bad, 'discussion 带 severity');
        assert_field_error('severity', $bad, 'discussion 带 severity');

        // 角标：数到这个话题，而且是我发起的
        $badge = raw('POST', '/v3/discussions/batch-count', null, ['targets' => [['type' => 'sentence', 'id' => discussion_target()]]], $token);
        if (($badge['body']['data'][0]['mine'] ?? 0) < 1) {
            fail('batch-count：mine 应至少为 1，body=' . json_encode($badge['body']));
        }

        // 列表里查得到
        $list = request(fn () => $client->getApiV3DiscussionsWithHttpInfo('sentence', discussion_target(), 50, 'issue'));
        assert_status(200, $list, '列表');
        $ids = array_column($list['body']['data'] ?? [], 'id');
        if (! in_array($id, $ids, true)) {
            fail('列表：查不到刚建的话题');
        }

        // 回复：201，类型跟着话题
        $reply = new PostApiV3DiscussionsDiscussionRepliesRequest();
        $reply->setContent('api-test 回复');
        $reply->setNotify(false);
        $r = request(fn () => $client->postApiV3DiscussionsDiscussionRepliesWithHttpInfo($id, $reply));
        assert_status(201, $r, '回复');
        $replyId = $r['body']['data']['id'] ?? null;
        assert_eq($id, $r['body']['data']['parent_id'] ?? null, '回复 parent_id');
        assert_eq('issue', $r['body']['data']['type'] ?? null, '回复类型跟话题');

        // 回复上改话题专属字段（标题、状态、类型）→ 422
        $topicOnly = raw('PATCH', "/v3/discussions/{$replyId}", null, ['title' => 'x'], $token);
        assert_field_error('title', $topicOnly, 'PATCH 回复的 title');

        // 不能回复一条回复 → 422
        $nested = raw('POST', "/v3/discussions/{$replyId}/replies", null, ['content' => 'x', 'notify' => false], $token);
        assert_status(422, $nested, '回复回复');

        $replies = request(fn () => $client->getApiV3DiscussionsDiscussionRepliesWithHttpInfo($id));
        assert_status(200, $replies, '回复列表');
        assert_eq([$replyId], array_column($replies['body']['data'] ?? [], 'id'), '回复列表内容');

        // 关闭 → 回复 409 → 重新打开
        $close = new PatchApiV3DiscussionsDiscussionRequest();
        $close->setStatus('close');
        $closed = request(fn () => $client->patchApiV3DiscussionsDiscussionWithHttpInfo($id, $close));
        assert_status(200, $closed, '关闭');
        assert_eq(false, $closed['body']['data']['abilities']['reply'] ?? null, '关闭后 abilities.reply');
        $conflict = raw('POST', "/v3/discussions/{$id}/replies", null, ['content' => 'x', 'notify' => false], $token);
        assert_status(409, $conflict, '关闭后回复');
        assert_problem($conflict, '关闭后回复');

        $open = new PatchApiV3DiscussionsDiscussionRequest();
        $open->setStatus('active');
        assert_status(200, request(fn () => $client->patchApiV3DiscussionsDiscussionWithHttpInfo($id, $open)), '重新打开');

        // 改类型：issue → discussion，severity 被清掉，回复跟着改
        $convert = new PatchApiV3DiscussionsDiscussionRequest();
        $convert->setType('discussion');
        $converted = request(fn () => $client->patchApiV3DiscussionsDiscussionWithHttpInfo($id, $convert));
        assert_status(200, $converted, '改类型');
        assert_eq(null, $converted['body']['data']['severity'] ?? null, '改类型后 severity');
        $reread = request(fn () => $client->getApiV3DiscussionsDiscussionWithHttpInfo($replyId));
        assert_eq('discussion', $reread['body']['data']['type'] ?? null, '回复类型跟着改');

        // 无 token 删：路由绑定先于鉴权，所以要拿真实存在的 id 才测得到 401
        $noToken = raw('DELETE', "/v3/discussions/{$replyId}");
        assert_status(401, $noToken, '无 token DELETE');
        assert_problem($noToken, '无 token DELETE');

        // 删回复：200 + 带 deleted_at 的资源；话题回复数归零
        $delReply = request(fn () => $client->deleteApiV3DiscussionsDiscussionWithHttpInfo($replyId));
        assert_status(200, $delReply, '删回复');
        if (empty($delReply['body']['data']['deleted_at'] ?? null)) {
            fail('删回复：响应应带 deleted_at');
        }
        $after = request(fn () => $client->getApiV3DiscussionsDiscussionWithHttpInfo($id));
        assert_eq(0, $after['body']['data']['reply_count'] ?? null, '删回复后 reply_count');
    } finally {
        // 无论中途哪步失败都清干净：先删剩下的回复，否则话题会留成墓碑
        $left = request(fn () => $client->getApiV3DiscussionsDiscussionRepliesWithHttpInfo($id));
        foreach (array_column($left['body']['data'] ?? [], 'id') as $rid) {
            request(fn () => $client->deleteApiV3DiscussionsDiscussionWithHttpInfo($rid));
        }
        $del = request(fn () => $client->deleteApiV3DiscussionsDiscussionWithHttpInfo($id));
    }
    assert_status(200, $del, '删话题');

    // 没有回复的话题删掉后不留墓碑 → 404
    $gone = raw('GET', "/v3/discussions/{$id}", null, null, $token);
    assert_status(404, $gone, '删后再查');
});
