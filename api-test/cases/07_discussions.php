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

/* ---- 术语讨论（glossary）与锚点 ---- */

test('glossary 一轮：带锚点建 → 公开可读 → 不收锚点的类型传锚点 422 → 改成 discussion 锚点保留 → 删', function () {
    $token = required_token();
    $stamp = 'api-test-glossary-' . date('Ymd-His');

    $create = raw('POST', '/v3/discussions', null, [
        'target_type' => 'sentence', 'target_id' => discussion_target(), 'type' => 'glossary',
        'title' => 'dukkha', 'content' => $stamp, 'pos_start' => 0, 'pos_end' => 3, 'quote_exact' => 'abc',
        'notify' => false,
    ], $token);
    assert_status(201, $create, '建 glossary');
    $id = $create['body']['data']['id'] ?? null;
    if (! $id) {
        fail('建 glossary：应返回 id');
    }

    try {
        assert_eq('dukkha', $create['body']['data']['title'] ?? null, 'glossary title 是词头');
        assert_eq(3, $create['body']['data']['pos_end'] ?? null, 'glossary pos_end');

        // 公开可见：匿名也读得到
        assert_status(200, raw('GET', "/v3/discussions/{$id}"), '匿名读 glossary');

        // glossary 缺 title / 挂在句子以外 → 422
        $noTitle = raw('POST', '/v3/discussions', null, [
            'target_type' => 'sentence', 'target_id' => discussion_target(), 'type' => 'glossary', 'notify' => false,
        ], $token);
        assert_field_error('title', $noTitle, 'glossary 缺 title');

        // discussion 不收锚点
        $anchored = raw('POST', '/v3/discussions', null, [
            'target_type' => 'sentence', 'target_id' => discussion_target(), 'type' => 'discussion',
            'title' => $stamp, 'pos_start' => 1, 'notify' => false,
        ], $token);
        assert_field_error('pos_start', $anchored, 'discussion 带锚点');

        // 改成 discussion：锚点保留（不显示，改回可带锚点的类型时恢复）
        $converted = raw('PATCH', "/v3/discussions/{$id}", null, ['type' => 'discussion'], $token);
        assert_status(200, $converted, 'glossary → discussion');
        assert_eq(3, $converted['body']['data']['pos_end'] ?? null, '改类型后 pos_end');
    } finally {
        $del = raw('DELETE', "/v3/discussions/{$id}", null, null, $token);
    }
    assert_status(200, $del, '删 glossary');
});

/* ---- 译文评估（assessment） ---- */

/*
 * 评估一句只留一条，再 POST 就覆盖，所以这里不删：测试句上始终至多一条评估，跑多少次都不会累积，
 * 留着还能在阅读页上看到句尾的对勾（本地开发库的测试数据要保留，见 mint 的 memory）。
 */
test('assessment 一轮：建或覆盖 → 再评估覆盖同一条 → 公开可读 → 分数 / 方法校验 422 → PATCH 改分', function () {
    $token = required_token();
    $stamp = 'api-test-assessment-' . date('Ymd-His');
    $post = fn (array $body) => raw('POST', '/v3/discussions', null, [
        'target_type' => 'sentence', 'target_id' => discussion_target(), 'type' => 'assessment', ...$body,
    ], $token);

    // 第一次：句上没有评估就 201，已有就 200 覆盖
    $first = $post(['score' => 55, 'severity' => 'api-test', 'content' => $stamp]);
    if (! in_array($first['status'], [200, 201], true)) {
        assert_status(201, $first, '建 assessment');
    }
    $id = $first['body']['data']['id'] ?? null;
    if (! $id) {
        fail('建 assessment：应返回 id');
    }
    assert_eq(55, $first['body']['data']['score'] ?? null, 'assessment score');
    assert_eq('api-test', $first['body']['data']['severity'] ?? null, 'assessment 方法存在 severity');

    // 再评估：覆盖同一条，200，id 不变
    $again = $post(['score' => 82, 'severity' => 'api-test', 'title' => '复评', 'content' => $stamp]);
    assert_status(200, $again, '再评估');
    assert_eq($id, $again['body']['data']['id'] ?? null, '再评估覆盖同一条');
    assert_eq(82, $again['body']['data']['score'] ?? null, '覆盖后的 score');

    // 公开：匿名也读得到；不带 type 的列表不列评估
    assert_status(200, raw('GET', "/v3/discussions/{$id}"), '匿名读 assessment');
    $listed = raw('GET', '/v3/discussions', ['target_type' => 'sentence', 'target_id' => discussion_target(), 'type' => 'assessment']);
    assert_eq([$id], array_column($listed['body']['data'] ?? [], 'id'), 'type=assessment 列出这一条');

    // 校验：缺分数、超范围、带小数、方法格式不对、其它类型带分数
    assert_field_error('score', $post(['severity' => 'api-test']), 'assessment 缺 score');
    assert_field_error('score', $post(['score' => 101, 'severity' => 'api-test']), 'assessment score 超范围');
    assert_field_error('score', $post(['score' => 80.5, 'severity' => 'api-test']), 'assessment score 只收整数');
    assert_field_error('severity', $post(['score' => 50, 'severity' => 'bad method']), 'assessment 方法格式');
    assert_field_error('score', raw('POST', '/v3/discussions', null, [
        'target_type' => 'sentence', 'target_id' => discussion_target(), 'type' => 'issue',
        'title' => $stamp, 'score' => 50, 'notify' => false,
    ], $token), 'issue 带 score');

    // PATCH 改分；评估没有开关
    $patched = raw('PATCH', "/v3/discussions/{$id}", null, ['score' => 91], $token);
    assert_status(200, $patched, 'PATCH assessment score');
    assert_eq(91, $patched['body']['data']['score'] ?? null, 'PATCH 后的 score');
    assert_field_error('status', raw('PATCH', "/v3/discussions/{$id}", null, ['status' => 'close'], $token), 'assessment 不能关闭');
});
