<?php

use App\Models\AgentRun;
use App\Models\AgentThread;
use App\Models\AiModel;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 agent-traces：AI 对话研究过程入库（服务间接口）。数据永久保留，没有清理命令。
 *
 *   POST /api/v3/agent-traces   X-Agent-Service-Key 必带；Bearer 是透传的终端用户 token（可选）
 *
 * 这里断言的是形状而非契约（spectator 尚未安装，见 v3-resource skill 第 3 步）。
 */
beforeEach(function () {
    config(['mint.app.agent_service_key' => 'test-agent-key']);
});

function agentHeaders(?string $userUid = null): array
{
    $headers = ['X-Agent-Service-Key' => 'test-agent-key'];

    return $userUid ? $headers + authHeader($userUid) : $headers;
}

function tracePayload(array $overrides = []): array
{
    return array_merge([
        'run_id' => (string) Str::uuid(),
        'thread_id' => (string) Str::uuid(),
        'device_id' => (string) Str::uuid(),
        'client_ip' => '203.0.113.7',
        'client' => 'mobile',
        'question' => '什么是四圣谛？',
        'deep' => false,
        'improve' => true,
        'route' => 'question',
        'plan' => ['lemmas' => ['ariyasacca']],
        'evidence' => '证据全文',
        'answer' => '四圣谛是……',
        'citations' => [['link' => 'https://wikipali.org/library/tipitaka/203-1677/read#1702']],
        'llm_calls' => 2,
        'tool_calls' => 3,
        'status' => 'ok',
        'user_message_id' => 'msg-u-1',
        'assistant_message_id' => 'msg-a-1',
        'started_at' => now()->toIso8601String(),
        'steps' => [
            ['seq' => 0, 'kind' => 'route', 'args' => ['route' => 'question']],
            ['seq' => 1, 'kind' => 'tool', 'name' => 'wikipali_search', 'args' => ['q' => 'ariyasacca'], 'result' => '…', 'result_chars' => 9000, 'ms' => 120],
            ['seq' => 2, 'kind' => 'llm', 'name' => 'deepseek-chat', 'tokens_in' => 1000, 'tokens_out' => 300, 'ms' => 2000],
        ],
    ], $overrides);
}

/* ------------------------------------------------------------------ 鉴权 */

it('rejects a request without the service key', function () {
    $this->postJson('/api/v3/agent-traces', tracePayload())->assertUnauthorized();
});

it('rejects a wrong service key', function () {
    $this->postJson('/api/v3/agent-traces', tracePayload(), ['X-Agent-Service-Key' => 'nope'])
        ->assertUnauthorized();
});

it('rejects everything when no service key is configured', function () {
    config(['mint.app.agent_service_key' => '']);

    $this->postJson('/api/v3/agent-traces', tracePayload(), ['X-Agent-Service-Key' => ''])
        ->assertUnauthorized();
});

it('rejects an end-user token without the service key', function () {
    $this->postJson('/api/v3/agent-traces', tracePayload(), authHeader((string) Str::uuid()))
        ->assertUnauthorized();
});

/* ------------------------------------------------------------------ 游客 */

it('records a guest run with device id and ip', function () {
    $payload = tracePayload();

    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())
        ->assertCreated()
        ->assertJsonPath('data.run_id', $payload['run_id'])
        ->assertJsonPath('data.thread_id', $payload['thread_id'])
        ->assertJsonPath('data.account_type', 'guest');

    $run = AgentRun::where('run_id', $payload['run_id'])->firstOrFail();
    expect($run->account_type)->toBe('guest')
        ->and($run->account_id)->toBe($payload['device_id'])
        ->and($run->client_ip)->toBe('203.0.113.7')
        ->and($run->plan)->toBe(['lemmas' => ['ariyasacca']])
        ->and($run->steps()->count())->toBe(3)
        ->and($run->thread->only('account_type', 'account_id'))->toBe(['account_type' => 'guest', 'account_id' => $payload['device_id']])
        ->and($run->thread->title)->toBe('什么是四圣谛？');
});

it('requires a device id from a guest', function () {
    $this->postJson('/api/v3/agent-traces', tracePayload(['device_id' => null]), agentHeaders())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('device_id');
});

it('falls back to guest when the user token is invalid', function () {
    $payload = tracePayload();

    $this->postJson('/api/v3/agent-traces', $payload, [
        'X-Agent-Service-Key' => 'test-agent-key',
        'Authorization' => 'Bearer not-a-jwt',
    ])->assertCreated()->assertJsonPath('data.account_type', 'guest');
});

it('treats a model token as a guest', function () {
    $human = makeStudio('owner');
    $model = AiModel::factory()->ownedBy($human)->create();
    $payload = tracePayload();

    $this->postJson('/api/v3/agent-traces', $payload, [
        'X-Agent-Service-Key' => 'test-agent-key',
        'Authorization' => 'Bearer '.AuthService::getUserToken($model->uid),
    ])->assertCreated()->assertJsonPath('data.account_type', 'guest');

    expect(AgentRun::where('run_id', $payload['run_id'])->value('account_type'))->toBe('guest');
});

/* ------------------------------------------------------------------ 登录用户 */

it('records a user run without device id or ip', function () {
    $user = (string) Str::uuid();
    $payload = tracePayload();

    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders($user))
        ->assertCreated()
        ->assertJsonPath('data.account_type', 'user');

    $run = AgentRun::where('run_id', $payload['run_id'])->firstOrFail();
    expect($run->account_type)->toBe('user')
        ->and($run->account_id)->toBe($user)
        ->and($run->client_ip)->toBeNull()
        ->and($run->thread->only('account_type', 'account_id'))->toBe(['account_type' => 'user', 'account_id' => $user]);
});

it('records a run even when the user opted out of improvement', function () {
    $payload = tracePayload(['improve' => false]);

    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertCreated();

    expect(AgentRun::where('run_id', $payload['run_id'])->value('improve'))->toBeFalse();
});

it('rejects a thread id that belongs to another account', function () {
    $user = (string) Str::uuid();
    $thread = (string) Str::uuid();

    $this->postJson('/api/v3/agent-traces', tracePayload(['thread_id' => $thread]), agentHeaders())->assertCreated();
    $this->postJson('/api/v3/agent-traces', tracePayload(['thread_id' => $thread]), agentHeaders($user))
        ->assertForbidden()
        ->assertJsonPath('status', 403);

    expect(AgentThread::where('thread_id', $thread)->count())->toBe(1)
        ->and(AgentThread::where('thread_id', $thread)->value('account_type'))->toBe('guest');
});

it('appends runs to the same thread and advances last_run_at', function () {
    $user = (string) Str::uuid();
    $thread = (string) Str::uuid();

    $this->postJson('/api/v3/agent-traces', tracePayload([
        'thread_id' => $thread, 'started_at' => now()->subHour()->toIso8601String(),
    ]), agentHeaders($user))->assertCreated();
    $this->postJson('/api/v3/agent-traces', tracePayload([
        'thread_id' => $thread, 'question' => '第二个问题',
    ]), agentHeaders($user))->assertCreated();

    $row = AgentThread::where('thread_id', $thread)->firstOrFail();
    expect($row->runs()->count())->toBe(2)
        ->and($row->title)->toBe('什么是四圣谛？')
        ->and($row->last_run_at->gt(now()->subMinutes(5)))->toBeTrue();
});

it('truncates a long first question into the title', function () {
    $payload = tracePayload(['question' => str_repeat('缘', 80)]);

    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertCreated();

    expect(AgentThread::where('thread_id', $payload['thread_id'])->value('title'))
        ->toBe(str_repeat('缘', 60).'…');
});

/* ------------------------------------------------------------------ 幂等与校验 */

it('is idempotent on run_id', function () {
    $payload = tracePayload();

    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertCreated();
    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertOk();

    expect(AgentRun::where('run_id', $payload['run_id'])->count())->toBe(1)
        ->and(AgentRun::where('run_id', $payload['run_id'])->first()->steps()->count())->toBe(3);
});

it('keeps a deleted run deleted when the same trace is sent again', function () {
    $payload = tracePayload();
    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertCreated();
    $run = AgentRun::where('run_id', $payload['run_id'])->first();
    $run->steps()->delete();
    $run->delete();

    // agent 端补发同一条：不重建、不复活
    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertOk();

    expect(AgentRun::withTrashed()->where('run_id', $payload['run_id'])->count())->toBe(1)
        ->and(AgentRun::where('run_id', $payload['run_id'])->exists())->toBeFalse();
});

it('links a regenerated answer to the round it replaces and hides the old one from history', function () {
    $user = (string) Str::uuid();
    $thread = (string) Str::uuid();
    $first = tracePayload(['thread_id' => $thread, 'assistant_message_id' => 'msg-a-old', 'answer' => '旧回答']);
    $this->postJson('/api/v3/agent-traces', $first, agentHeaders($user))->assertCreated();

    $again = tracePayload(['thread_id' => $thread, 'assistant_message_id' => 'msg-a-new', 'answer' => '新回答', 'replaces_message_id' => 'msg-a-old']);
    $this->postJson('/api/v3/agent-traces', $again, agentHeaders($user))->assertCreated();

    expect(AgentRun::where('run_id', $again['run_id'])->value('replaces_run_id'))->toBe($first['run_id']);
    $this->getJson("/api/v3/me/agent-threads/{$thread}", authHeader($user))
        ->assertOk()
        ->assertJsonCount(1, 'data.runs')
        ->assertJsonPath('data.runs.0.answer', '新回答');
});

it('records a denied run that never reached the agent', function () {
    $payload = tracePayload(['status' => 'denied', 'answer' => null, 'evidence' => null, 'steps' => []]);

    $this->postJson('/api/v3/agent-traces', $payload, agentHeaders())->assertCreated();

    expect(AgentRun::where('run_id', $payload['run_id'])->value('status'))->toBe('denied');
});

it('rejects an unknown status', function () {
    $this->postJson('/api/v3/agent-traces', tracePayload(['status' => 'weird']), agentHeaders())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('rejects a step kind longer than the column (unknown kinds are kept, see AgentRouteEnumTest)', function () {
    $this->postJson('/api/v3/agent-traces', tracePayload([
        'steps' => [['seq' => 0, 'kind' => str_repeat('k', 17)]],
    ]), agentHeaders())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('steps.0.kind');
});
