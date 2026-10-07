<?php

use \WikipaliApi\Api\MeApi;
use \WikipaliApi\Api\ReactionsApi;
use \WikipaliApi\Model\PostApiV3MeReactionsRequest;

/* ---- 公共 reactions / tally（只读，无需 token）---- */

test('GET /v3/reactions?target_id=… → 200', function () {
    $res = request(fn () => api(ReactionsApi::class)->getApiV3ReactionsWithHttpInfo(fixtures()['target_id']));
    assert_status(200, $res, 'reactions');
    assert_has_key('data', $res['body'], 'reactions');
    assert_has_key('meta', $res['body'], 'reactions');
});

test('GET /v3/reactions（缺 target_id）→ 422', function () {
    $res = raw('GET', '/v3/reactions');
    assert_status(422, $res, 'reactions 缺 target_id');
    assert_problem($res, 'reactions 缺 target_id');
});

test('GET /v3/reactions/tally?target_id=… → 200', function () {
    $res = request(fn () => api(ReactionsApi::class)->getApiV3ReactionsTallyWithHttpInfo(fixtures()['target_id']));
    assert_status(200, $res, 'tally');
    assert_has_key('data', $res['body'], 'tally');
    $first = $res['body']['data'][0] ?? null;
    assert_has_key('type', $first, 'tally.data[0]');
    assert_has_key('count', $first, 'tally.data[0]');
});

test('GET /v3/reactions/tally（缺 target_id）→ 422', function () {
    $res = raw('GET', '/v3/reactions/tally');
    assert_status(422, $res, 'tally 缺 target_id');
    assert_problem($res, 'tally 缺 target_id');
});

/* ---- /v3/me/reactions：无 token → 401（每个动作各一条）---- */

test('GET /v3/me/reactions（无 token）→ 401', function () {
    $res = request(fn () => api(MeApi::class)->getApiV3MeReactionsWithHttpInfo());
    assert_status(401, $res, 'me/reactions 无 token');
    assert_problem($res, 'me/reactions 无 token');
});

test('POST /v3/me/reactions（无 token）→ 401', function () {
    $body = new PostApiV3MeReactionsRequest();
    $body->setType('watch');
    $body->setTargetId('11111111-1111-4111-8111-111111111111');
    $body->setTargetType('collection');
    $res = request(fn () => api(MeApi::class)->postApiV3MeReactionsWithHttpInfo($body));
    assert_status(401, $res, 'me/reactions POST 无 token');
    assert_problem($res, 'me/reactions POST 无 token');
});

test('GET /v3/me/reactions（非法 token）→ 401', function () {
    $res = request(fn () => api(MeApi::class, 'garbage-token')->getApiV3MeReactionsWithHttpInfo());
    assert_status(401, $res, 'me/reactions 非法 token');
    assert_problem($res, 'me/reactions 非法 token');
});

/* ---- /v3/me/reactions：带 token → 实际增删查（幂等 + 无 token 删 401 + 删后 404）---- */

test('me/reactions 增删查：POST 建 → GET 查到 → 再 POST 幂等 → 无 token DELETE 401 → DELETE 204 → 再 DELETE 404', function () {
    $token = required_token();

    // 垃圾 target（uuid 格式即可，不要求真实存在），测完删掉
    $targetId = 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee';
    $body = new PostApiV3MeReactionsRequest();
    $body->setType('watch');
    $body->setTargetId($targetId);
    $body->setTargetType('collection');

    // 增：POST
    $create = request(fn () => api(MeApi::class, $token)->postApiV3MeReactionsWithHttpInfo($body));
    if (! in_array($create['status'], [200, 201], true)) {
        fail('POST 创建：期望 201（新建）或 200（已存在），实际 ' . $create['status']);
    }
    $id = $create['body']['data']['id'] ?? null;
    if (! $id) {
        fail('POST 创建：应返回 reaction id');
    }

    // 查：GET 按 type 过滤，能查到刚建的（用 target_id 定位，因为列表没有 target_id 过滤参数）
    $list = request(fn () => api(MeApi::class, $token)->getApiV3MeReactionsWithHttpInfo(200, 'watch'));
    assert_status(200, $list, 'GET 列表');
    $found = false;
    foreach (($list['body']['data'] ?? []) as $row) {
        if (($row['target_id'] ?? null) === $targetId) {
            $found = true;
            break;
        }
    }
    if (! $found) {
        fail('GET 列表：查不到刚建的 reaction（target_id=' . $targetId . '）');
    }

    // 幂等：再 POST 同一条 → 200，id 不变
    $again = request(fn () => api(MeApi::class, $token)->postApiV3MeReactionsWithHttpInfo($body));
    assert_status(200, $again, '再 POST 幂等');
    assert_eq($id, $again['body']['data']['id'] ?? null, '幂等：两次 id 一致');

    // 无 token DELETE → 401（此时该 reaction 存在，路由绑定成功 → 鉴权 401）
    $noTokenDel = raw('DELETE', '/v3/me/reactions/' . $id);
    assert_status(401, $noTokenDel, '无 token DELETE');
    assert_problem($noTokenDel, '无 token DELETE');

    // 删：DELETE → 204
    $del = request(fn () => api(MeApi::class, $token)->deleteApiV3MeReactionsReactionWithHttpInfo($id));
    assert_status(204, $del, 'DELETE');

    // 再删：已删除 → 404
    $delAgain = request(fn () => api(MeApi::class, $token)->deleteApiV3MeReactionsReactionWithHttpInfo($id));
    assert_status(404, $delAgain, '再 DELETE');
    assert_problem($delAgain, '再 DELETE');
});
