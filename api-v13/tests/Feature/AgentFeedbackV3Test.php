<?php

use App\Models\AgentEvent;
use App\Models\AgentFeedback;
use App\Models\AgentRun;
use App\Models\AgentThread;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 AI 回答评价、隐式信号、自动质量指标与分析导出：
 *
 *   POST   /api/v3/agent-feedback               评价（可选登录，游客带 device_id）
 *   DELETE /api/v3/agent-feedback/{message}     撤回
 *   POST   /api/v3/agent-events                 隐式信号
 *   GET    /api/v3/admin/agent-feedback         管理员审阅
 *   GET    /api/v3/admin/agent-runs/digest      分析用精简导出
 *
 * 这里断言的是形状而非契约（spectator 尚未安装，见 v3-resource skill 第 3 步）。
 */
function feedbackAdmin(): array
{
    $uid = makeStudio('fa-'.Str::random(5));
    UserInfo::where('userid', $uid)->update(['role' => json_encode(['administrator'])]);

    return authHeader($uid);
}

/* ------------------------------------------------------------------ 评价 */

it('lets a guest rate an answer and change the rating', function () {
    $device = (string) Str::uuid();

    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'lc_run--abc', 'rating' => 1, 'tags' => ['clear'], 'device_id' => $device])
        ->assertCreated()
        ->assertJsonPath('data.rating', 1)
        ->assertJsonPath('data.tags', ['clear']);
    $this->postJson('/api/v3/agent-feedback', [
        'message_id' => 'lc_run--abc', 'rating' => -1, 'tags' => ['missed', 'other'], 'comment' => '中部有这段', 'device_id' => $device,
    ])->assertOk()->assertJsonPath('data.comment', '中部有这段');

    expect(AgentFeedback::count())->toBe(1)
        ->and(AgentFeedback::first()->only('account_type', 'account_id'))->toBe(['account_type' => 'guest', 'account_id' => $device]);
});

it('keeps one rating per user and per guest device', function () {
    $user = (string) Str::uuid();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm1', 'rating' => 1], authHeader($user))->assertCreated();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm1', 'rating' => -1, 'device_id' => (string) Str::uuid()])->assertCreated();

    expect(AgentFeedback::where('message_id', 'm1')->count())->toBe(2);
});

it('drops the comment unless other is chosen', function () {
    $this->postJson('/api/v3/agent-feedback', [
        'message_id' => 'm2', 'rating' => -1, 'tags' => ['slow'], 'comment' => 'x', 'device_id' => (string) Str::uuid(),
    ])->assertCreated()->assertJsonPath('data.comment', null);
});

it('rejects tags that do not match the rating', function () {
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm3', 'rating' => 1, 'tags' => ['slow'], 'device_id' => (string) Str::uuid()])
        ->assertUnprocessable()->assertJsonValidationErrors('tags');
});

it('rejects an unknown tag and a bad message id', function () {
    $device = (string) Str::uuid();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm4', 'rating' => -1, 'tags' => ['boring'], 'device_id' => $device])
        ->assertUnprocessable();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'a b', 'rating' => 1, 'device_id' => $device])
        ->assertUnprocessable()->assertJsonValidationErrors('message_id');
});

it('requires a device id from guests', function () {
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm5', 'rating' => 1])
        ->assertUnprocessable()->assertJsonValidationErrors('device_id');
});

it('remembers that a rating was asked for', function () {
    $device = (string) Str::uuid();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm6', 'rating' => 1, 'asked' => true, 'device_id' => $device]);
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm6', 'rating' => -1, 'device_id' => $device])
        ->assertJsonPath('data.asked', true);
});

it('retracts a rating idempotently', function () {
    $device = (string) Str::uuid();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'lc_run--x', 'rating' => 1, 'device_id' => $device]);

    $this->deleteJson("/api/v3/agent-feedback/lc_run--x?device_id={$device}")->assertNoContent();
    $this->deleteJson("/api/v3/agent-feedback/lc_run--x?device_id={$device}")->assertNoContent();
    expect(AgentFeedback::count())->toBe(0);
});

it('cannot retract someone else\'s rating', function () {
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm7', 'rating' => 1, 'device_id' => (string) Str::uuid()]);

    $this->deleteJson('/api/v3/agent-feedback/m7?device_id='.Str::uuid())->assertNoContent();
    expect(AgentFeedback::count())->toBe(1);
});

/* ------------------------------------------------------------------ 隐式信号 */

it('records implicit signals', function () {
    $this->postJson('/api/v3/agent-events', ['message_id' => 'm8', 'kind' => 'copy', 'device_id' => (string) Str::uuid(), 'client' => 'web'])
        ->assertCreated()->assertJsonPath('data.kind', 'copy');
    $this->postJson('/api/v3/agent-events', ['message_id' => 'm8', 'kind' => 'citation_open'], authHeader((string) Str::uuid()))
        ->assertCreated();

    expect(AgentEvent::count())->toBe(2);
});

it('rejects an unknown signal', function () {
    $this->postJson('/api/v3/agent-events', ['message_id' => 'm9', 'kind' => 'like', 'device_id' => (string) Str::uuid()])
        ->assertUnprocessable();
});

/* ------------------------------------------------------------------ 评价即同意用于改进 */

it('turns improve on for the rated run of the same account only', function () {
    $device = (string) Str::uuid();
    $mine = AgentThread::factory()->forGuest($device)->create();
    $run = AgentRun::factory()->for($mine, 'thread')->create(['assistant_message_id' => 'msg-c1', 'improve' => false]);
    $other = AgentRun::factory()->for(AgentThread::factory(), 'thread')->create(['assistant_message_id' => 'msg-c2', 'improve' => false]);

    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'msg-c1', 'rating' => 1, 'device_id' => $device])->assertCreated();
    // 别人的回答（消息 id 碰巧一样也不行）不受影响
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'msg-c2', 'rating' => -1, 'device_id' => $device])->assertCreated();

    expect($run->fresh()->improve)->toBeTrue()
        ->and($other->fresh()->improve)->toBeFalse();
});

it('marks a trace improve when the rating arrived first', function () {
    config(['mint.app.agent_service_key' => 'k']);
    $device = (string) Str::uuid();
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'msg-early', 'rating' => 1, 'device_id' => $device])->assertCreated();

    $run = (string) Str::uuid();
    $this->postJson('/api/v3/agent-traces', [
        'run_id' => $run, 'thread_id' => (string) Str::uuid(), 'device_id' => $device, 'improve' => false,
        'question' => '什么是慈？', 'status' => 'ok', 'answer' => '……', 'assistant_message_id' => 'msg-early',
    ], ['X-Agent-Service-Key' => 'k'])->assertCreated();

    expect(AgentRun::where('run_id', $run)->value('improve'))->toBeTrue();
});

/* ------------------------------------------------------------------ 管理员 */

it('lists feedback with the run summary for admins only', function () {
    $thread = AgentThread::factory()->create();
    AgentRun::factory()->for($thread, 'thread')->create(['assistant_message_id' => 'msg-1', 'question' => '什么是慈？']);
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'msg-1', 'rating' => -1, 'tags' => ['missed'], 'device_id' => (string) Str::uuid()]);
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'msg-unknown', 'rating' => 1, 'device_id' => (string) Str::uuid()]);

    $this->getJson('/api/v3/admin/agent-feedback')->assertUnauthorized();
    $this->getJson('/api/v3/admin/agent-feedback', authHeader(makeStudio('np-'.Str::random(4))))->assertForbidden();

    $admin = feedbackAdmin();
    $this->getJson('/api/v3/admin/agent-feedback?filter[rating]=-1', $admin)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.question', '什么是慈？')
        ->assertJsonPath('data.0.account_type', 'guest')
        ->assertJsonMissingPath('data.0.account')
        // 对外不暴露自增主键
        ->assertJsonMissingPath('data.0.id');
    $this->getJson('/api/v3/admin/agent-feedback?filter[tag]=missed', $admin)->assertJsonCount(1, 'data');
    $this->getJson('/api/v3/admin/agent-feedback', $admin)->assertJsonPath('meta.total', 2);
});

it('adds feedback to the agent stats', function () {
    $thread = AgentThread::factory()->create();
    AgentRun::factory()->for($thread, 'thread')->create([
        'assistant_message_id' => 'msg-s', 'started_at' => '2026-10-20 10:00:00', 'route' => 'question',
    ]);
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'msg-s', 'rating' => -1, 'tags' => ['citation_wrong', 'slow'], 'asked' => true, 'device_id' => (string) Str::uuid()]);

    $data = $this->getJson('/api/v3/admin/agent-stats?from=2026-10-20&to=2026-10-20', feedbackAdmin())->json('data');

    expect($data['summary'])->toMatchArray(['feedback_down' => 1, 'feedback_asked' => 1])
        ->and($data['summary'])->not->toHaveKey('quality_assessed')
        ->and($data)->not->toHaveKey('quality_by_route')
        ->and(collect($data['feedback_reasons'])->pluck('reason')->all())->toContain('citation_wrong', 'slow')
        ->and($data['daily'][0]['feedback_down'])->toBe(1);
});

it('exports a digest without identities or opted-out runs', function () {
    $user = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($user)->create();
    AgentRun::factory()->for($thread, 'thread')->create([
        'assistant_message_id' => 'd1', 'started_at' => '2026-10-20 10:00:00', 'question' => 'Q1',
        'plan' => ['lemmas' => ['mettā'], 'keywords' => [['lemma' => 'mettā', 'tags' => 'sutta']]],
    ]);
    AgentRun::factory()->for($thread, 'thread')->create(['started_at' => '2026-10-20 10:00:30', 'question' => 'Q2']);
    AgentRun::factory()->for($thread, 'thread')->create(['started_at' => '2026-10-20 10:05:00', 'question' => 'secret', 'improve' => false]);
    $this->postJson('/api/v3/agent-events', ['message_id' => 'd1', 'kind' => 'copy'], authHeader($user));

    $res = $this->getJson('/api/v3/admin/agent-runs/digest?from=2026-10-20&to=2026-10-20', feedbackAdmin())->assertOk();
    $items = $res->json('data');

    expect(array_column($items, 'question'))->toBe(['Q1', 'Q2'])
        ->and($items[0])->toMatchArray(['lemmas' => ['mettā'], 'keywords' => ['mettā|sutta'], 'events' => ['copy' => 1], 'next_question_after_s' => 30])
        ->and($items[0])->not->toHaveKeys(['user', 'user_uid', 'device_id', 'client_ip', 'account_id', 'answer', 'evidence'])
        ->and($res->json('meta.has_more'))->toBeFalse();
});

it('adds answer, evidence and citations to the digest on request for agent evaluation', function () {
    $thread = AgentThread::factory()->create();
    AgentRun::factory()->for($thread, 'thread')->create([
        'started_at' => '2026-10-20 10:00:00', 'answer' => 'A', 'evidence' => 'E', 'citations' => [['link' => 'https://x/1-2']],
    ]);
    $admin = feedbackAdmin();

    $item = $this->getJson('/api/v3/admin/agent-runs/digest?from=2026-10-20&to=2026-10-20&full=1', $admin)->assertOk()->json('data.0');
    expect($item)->toMatchArray(['answer' => 'A', 'evidence' => 'E', 'citations' => [['link' => 'https://x/1-2']]])
        ->and($item)->not->toHaveKey('quality');

    // 带全文时条数上限收紧到 200
    $this->getJson('/api/v3/admin/agent-runs/digest?full=1&limit=201', $admin)->assertUnprocessable()->assertJsonValidationErrors('limit');
    $this->getJson('/api/v3/admin/agent-runs/digest?limit=201', $admin)->assertOk();
});

it('leaves soft-deleted runs out of every admin read', function () {
    $thread = AgentThread::factory()->create();
    $kept = AgentRun::factory()->for($thread, 'thread')->create(['started_at' => '2026-10-20 10:00:00', 'question' => 'kept', 'assistant_message_id' => 'm-kept']);
    $gone = AgentRun::factory()->for($thread, 'thread')->create(['started_at' => '2026-10-20 11:00:00', 'question' => 'gone', 'assistant_message_id' => 'm-gone']);
    $gone->steps()->create(['seq' => 0, 'kind' => 'tool', 'name' => 'wikipali_search']);
    $this->postJson('/api/v3/agent-feedback', ['message_id' => 'm-gone', 'rating' => -1, 'device_id' => (string) Str::uuid()]);
    $gone->steps()->delete();
    $gone->delete();
    $admin = feedbackAdmin();

    expect($this->getJson('/api/v3/admin/agent-runs', $admin)->json('data.*.question'))->toBe(['kept']);
    $this->getJson("/api/v3/admin/agent-runs/{$gone->run_id}", $admin)->assertNotFound();
    expect($this->getJson('/api/v3/admin/agent-runs/digest?from=2026-10-20&to=2026-10-20', $admin)->json('data.*.question'))->toBe(['kept']);
    $stats = $this->getJson('/api/v3/admin/agent-stats?from=2026-10-20&to=2026-10-20', $admin)->json('data');
    expect($stats['summary']['runs'])->toBe(1)
        ->and($stats['tools'])->toBe([]);
    // 评价本身还在，只是不再带出那次问答
    $this->getJson('/api/v3/admin/agent-feedback', $admin)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.run_id', null)
        ->assertJsonPath('data.0.question', null);
});
