<?php

use App\Http\Controllers\V3\AdminAgentRunController;
use App\Http\Resources\V3\AdminAgentFeedbackResource;
use App\Http\Resources\V3\AdminAgentRunResource;
use App\Models\AgentRun;
use App\Models\AgentRunStep;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 研究路径枚举（AgentRun::ROUTES）的唯一来源守卫，以及入库对未知路由 / 步骤类型的宽容。
 *
 * OpenAPI 的枚举来自 Resource `@return` 与控制器 `@queryParam … Enum:` 里写的字面量（PHPDoc 引用不了常量），
 * 这里断言它们与常量一致；backend 的离线测试再拿 OpenAPI 规格核对 routing.py。
 */
it('declares the same route enum in every doc the OpenAPI spec is generated from', function () {
    foreach ([AdminAgentRunResource::class, AdminAgentFeedbackResource::class] as $resource) {
        $doc = (new ReflectionMethod($resource, 'toArray'))->getDocComment();
        expect(preg_match("/\\broute: ((?:'[a-z_]+'\\|)+)null/", $doc, $m))->toBe(1, "{$resource} 的 route 没写成字面量联合")
            ->and(explode('|', str_replace("'", '', rtrim($m[1], '|'))))->toBe(AgentRun::ROUTES);
    }

    $doc = (new ReflectionMethod(AdminAgentRunController::class, 'index'))->getDocComment();
    expect(preg_match('/@queryParam filter\[route\].*Enum: ([a-z_,]+)/', $doc, $m))->toBe(1)
        ->and(explode(',', $m[1]))->toBe(AgentRun::ROUTES);
});

it('keeps a trace with a route or step kind it does not know yet, and warns', function () {
    config(['mint.app.agent_service_key' => 'k']);
    Log::spy();
    $run = (string) Str::uuid();

    $this->postJson('/api/v3/agent-traces', [
        'run_id' => $run, 'thread_id' => (string) Str::uuid(), 'device_id' => (string) Str::uuid(),
        'question' => '新路由', 'status' => 'ok', 'route' => 'compare', 'answer' => '……',
        'steps' => [['seq' => 0, 'kind' => 'rerank', 'name' => 'x'], ['seq' => 1, 'kind' => 'tool', 'name' => 'wikipali_search']],
    ], ['X-Agent-Service-Key' => 'k'])->assertCreated();

    expect(AgentRun::where('run_id', $run)->value('route'))->toBe('compare')
        ->and(AgentRunStep::where('agent_run_id', AgentRun::where('run_id', $run)->value('id'))->orderBy('seq')->pluck('kind')->all())->toBe(['rerank', 'tool']);
    Log::shouldHaveReceived('warning')->with('agent trace: unknown route', Mockery::on(fn ($c) => $c['route'] === 'compare'))->once();
    Log::shouldHaveReceived('warning')->with('agent trace: unknown step kinds', Mockery::on(fn ($c) => $c['kinds'] === ['rerank']))->once();
});

it('rejects an unknown route in the admin filter', function () {
    $uid = makeStudio('re-'.Str::random(5));
    UserInfo::where('userid', $uid)->update(['role' => json_encode(['administrator'])]);

    $this->getJson('/api/v3/admin/agent-runs?filter[route]=compare', authHeader($uid))->assertUnprocessable();
    $this->getJson('/api/v3/admin/agent-runs?filter[route]=term', authHeader($uid))->assertOk();
});
