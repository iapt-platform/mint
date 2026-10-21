<?php

use \WikipaliApi\Api\TipitakaDiscussionsApi;

/*
 * 按经文范围列讨论：GET /v3/tipitaka-discussions/{channel}?book=&chapter=|para=&to=&type=…
 *
 * 只读端点。完整一轮借 discussions 的写接口在 fixtures 的 discussion_sentence 上建一条
 * issue（测试账号自己的句子，notify=false），按它所在的段落列出来核对，最后删掉。
 * 权限矩阵（匿名 / basic 可见性）、排序、墓碑由 api-v13 的 TipitakaDiscussionV3Test 覆盖。
 */

/** fixture 句子的 book / paragraph（从 v2 单句接口取，各服务器的 fixture 不同） */
function tipitaka_discussion_coord(): array
{
    $res = raw('GET', '/v2/sentence/' . fixtures()['discussion_sentence']);
    assert_status(200, $res, '取 fixture 句子');
    $data = $res['body']['data'] ?? [];

    return [(int) ($data['book'] ?? 0), (int) ($data['paragraph'] ?? 0)];
}

test('GET /v3/tipitaka-discussions/{channel} → 200，列表 + 分页', function () {
    $res = request(fn () => api(TipitakaDiscussionsApi::class)
        ->getApiV3TipitakaDiscussionsChannelWithHttpInfo(fixtures()['discussion_channel']));
    assert_status(200, $res, 'tipitaka-discussions');
    assert_has_key('data', $res['body'], 'tipitaka-discussions');
    assert_has_key('meta', $res['body'], 'tipitaka-discussions');
});

test('GET /v3/tipitaka-discussions/{channel}（chapter 不带 book）→ 422', function () {
    $res = raw('GET', '/v3/tipitaka-discussions/' . fixtures()['discussion_channel'], ['chapter' => 1]);
    assert_status(422, $res, 'chapter 不带 book');
    assert_field_error('book', $res, 'chapter 不带 book');
});

test('GET /v3/tipitaka-discussions/{channel}（非法 type）→ 422', function () {
    $res = raw('GET', '/v3/tipitaka-discussions/' . fixtures()['discussion_channel'], ['type' => 'issue,bogus']);
    assert_status(422, $res, '非法 type');
    assert_field_error('type', $res, '非法 type');
});

test('GET /v3/tipitaka-discussions/{非 uuid} → 404', function () {
    assert_status(404, raw('GET', '/v3/tipitaka-discussions/not-a-uuid'), '非 uuid 的 channel');
});

test('tipitaka-discussions 一轮：在 fixture 句子上建 issue → 按段落列出、带句子坐标 → 按类型过滤 → 删', function () {
    $token = required_token();
    [$book, $para] = tipitaka_discussion_coord();
    $stamp = 'api-test-tipitaka-discussions-' . date('Ymd-His');

    $create = raw('POST', '/v3/discussions', null, [
        'target_type' => 'sentence', 'target_id' => fixtures()['discussion_sentence'],
        'type' => 'issue', 'severity' => 'suggestion', 'title' => $stamp,
        'content' => '由 api-test 建立，测完即删', 'notify' => false,
    ], $token);
    assert_status(201, $create, '建 issue');
    $id = $create['body']['data']['id'] ?? null;
    if (! $id) {
        fail('建 issue：应返回 id');
    }

    try {
        $client = api(TipitakaDiscussionsApi::class, $token);
        $list = request(fn () => $client->getApiV3TipitakaDiscussionsChannelWithHttpInfo(
            fixtures()['discussion_channel'], 200, $book, null, $para, null, 'issue'));
        assert_status(200, $list, '按段落列 issue');
        $hit = null;
        foreach ($list['body']['data'] ?? [] as $row) {
            if (($row['id'] ?? null) === $id) {
                $hit = $row;
            }
        }
        if (! $hit) {
            fail('按段落列 issue：应包含刚建的那条');
        }
        assert_eq($book, $hit['sentence']['book'] ?? null, '句子坐标 book');
        assert_eq($para, $hit['sentence']['paragraph'] ?? null, '句子坐标 paragraph');

        // 按别的类型过滤就不该出现
        $glossary = request(fn () => $client->getApiV3TipitakaDiscussionsChannelWithHttpInfo(
            fixtures()['discussion_channel'], 200, $book, null, $para, null, 'glossary'));
        assert_status(200, $glossary, '按 glossary 过滤');
        assert_eq(false, in_array($id, array_column($glossary['body']['data'] ?? [], 'id'), true), '按 glossary 过滤不含 issue');
    } finally {
        $del = raw('DELETE', "/v3/discussions/{$id}", null, null, $token);
    }
    assert_status(200, $del, '删 issue');
});
