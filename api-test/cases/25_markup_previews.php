<?php

use \WikipaliApi\Api\MarkupPreviewsApi;
use \WikipaliApi\Model\PostApiV3MarkupPreviewsRequest;

/*
 * 源码预览：POST /v3/markup-previews（模板生成器的预览）。要登录，不写库。
 * 各服务器数据不同，只核对形状；按 channel 查术语、私有 channel 403 由 api-v13 的 MarkupPreviewV3Test 覆盖。
 */

test('POST /v3/markup-previews → 200，body 是节点树', function () {
    $token = required_token();
    $body = new PostApiV3MarkupPreviewsRequest();
    $body->setContent("**粗体** {{quality|featured}}\n\n{{v|url=https://example.com/a.mp4|title=x}}");
    $res = request(fn () => api(MarkupPreviewsApi::class, $token)->postApiV3MarkupPreviewsWithHttpInfo($body));
    assert_status(200, $res, 'markup preview');
    $nodes = $res['body']['data']['body'] ?? null;
    assert_eq(true, is_array($nodes) && count($nodes) > 0, 'body 不为空');
    $json = json_encode($nodes);
    assert_eq([true, true], [str_contains($json, '"name":"quality"'), str_contains($json, '"name":"video"')], '模板节点');
});

test('POST /v3/markup-previews：无 token → 401，缺 content → 422', function () {
    $anon = raw('POST', '/v3/markup-previews', null, ['content' => 'x']);
    assert_status(401, $anon, '无 token');
    $bad = raw('POST', '/v3/markup-previews', null, ['content_type' => 'html'], required_token());
    assert_status(422, $bad, '缺 content');
    assert_field_error('content', $bad, '缺 content');
});
