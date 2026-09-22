<?php

/**
 * GET /api/v3/heartbeat —— 心跳（liveness），不是健康检查。
 * 真正的健康检查是 /v2/health-check，逐项探测外部依赖，将来单独迁移。
 *
 * 不用 RefreshDatabase：这个端点不碰数据库，也不需要任何夹具。
 *
 * 注意：这里断言的是响应形状，不是契约。项目尚未安装 hotmeteor/spectator，
 * 所以无法对着生成的 OpenAPI 规格做校验——规格与实现漂移不会被自动拦截。
 * 装上之后应把这些断言换成 assertValidResponse()。
 */
afterEach(function () {
    @unlink(base_path('.stop'));
});

it('reports that the process is serving', function () {
    $response = $this->getJson('/api/v3/heartbeat')->assertOk();

    expect($response->json('data.status'))->toBe('ok');
    // checked_at 必须是可解析的 ISO 8601 UTC 时间
    expect(strtotime($response->json('data.checked_at')))->toBeGreaterThan(0);
    expect($response->json('data.checked_at'))->toEndWith('Z');
});

it('needs no authentication', function () {
    // 不带任何 Authorization 头也应当通过
    $this->getJson('/api/v3/heartbeat')->assertOk();
});

it('returns 503 with problem details while the stop switch is on', function () {
    touch(base_path('.stop'));

    $this->getJson('/api/v3/heartbeat')
        ->assertStatus(503)
        ->assertJson([
            'title' => 'Service unavailable.',
            'status' => 503,
        ])
        ->assertJsonStructure(['type', 'title', 'status', 'detail']);
});

it('localizes the problem detail instead of hardcoding it', function () {
    touch(base_path('.stop'));

    $detail = $this->getJson('/api/v3/heartbeat')->assertStatus(503)->json('detail');

    // 翻译文件缺失时 __() 会原样吐出键名，这条断言守住这种退化
    expect($detail)->not->toBe('site.maintenance');
    expect($detail)->toBe(__('site.maintenance'));

    // 中文翻译必须存在
    expect(trans('site.maintenance', [], 'zh-Hans'))->toBe('服务已进入停机维护状态');
});

it('recovers once the stop switch is removed', function () {
    touch(base_path('.stop'));
    $this->getJson('/api/v3/heartbeat')->assertStatus(503);

    unlink(base_path('.stop'));
    $this->getJson('/api/v3/heartbeat')->assertOk();
});

it('does not touch the frozen v2 heartbeat endpoint', function () {
    // v2 迁移期内必须原样可用：裸返回 createdAt，不带 data 包装
    $response = $this->getJson('/api/v2/heartbeat')->assertOk();

    expect($response->json('createdAt'))->not->toBeNull();
    expect($response->json('data'))->toBeNull();
});
