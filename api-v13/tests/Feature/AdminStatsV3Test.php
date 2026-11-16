<?php

use App\Models\AgentRun;
use App\Models\AgentThread;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 管理后台统计（仅 root / administrator）：
 *
 *   GET /api/v3/admin/reading-stats   阅读统计
 *   GET /api/v3/admin/agent-stats     AI 对话统计
 *   GET /api/v3/admin/agent-runs      提问列表
 *   GET /api/v3/admin/agent-runs/{run}  研究过程
 *
 * 这里断言的是形状而非契约（spectator 尚未安装，见 v3-resource skill 第 3 步）。
 */
function makeAdmin(string $role = 'administrator'): string
{
    $uid = makeStudio('admin-'.Str::random(6));
    UserInfo::where('userid', $uid)->update(['role' => json_encode([$role, 'member'])]);

    return $uid;
}

function addRecent(string $user, string $article, string $mode, string $at): void
{
    DB::table('recents')->insert([
        'id' => (string) Str::uuid(), 'type' => 'chapter', 'article_id' => $article,
        'param' => json_encode(['mode' => $mode]), 'user_uid' => $user, 'created_at' => $at, 'updated_at' => $at,
    ]);
}

/* ------------------------------------------------------------------ 权限 */

it('requires login for admin stats', function (string $path) {
    $this->getJson("/api/v3/admin/{$path}")->assertUnauthorized();
})->with(['reading-stats', 'agent-stats', 'agent-runs']);

it('forbids non-admin users', function (string $path) {
    $this->getJson("/api/v3/admin/{$path}", authHeader(makeStudio('plain-'.Str::random(5))))->assertForbidden();
})->with(['reading-stats', 'agent-stats', 'agent-runs']);

it('lets the root account in', function () {
    $this->getJson('/api/v3/admin/agent-stats', authHeader(config('mint.admin.root_uuid')))->assertOk();
});

it('lets administrators in', function () {
    $this->getJson('/api/v3/admin/reading-stats', authHeader(makeAdmin()))->assertOk();
});

/* ------------------------------------------------------------------ 区间 */

it('defaults to the last 30 days', function () {
    $this->getJson('/api/v3/admin/agent-stats', authHeader(makeAdmin()))
        ->assertOk()
        ->assertJsonPath('data.range.to', now('UTC')->toDateString())
        ->assertJsonPath('data.range.from', now('UTC')->subDays(29)->toDateString())
        ->assertJsonCount(30, 'data.daily');
});

it('rejects a range longer than 366 days', function () {
    $this->getJson('/api/v3/admin/reading-stats?from=2024-01-01&to=2026-01-01', authHeader(makeAdmin()))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('from');
});

it('rejects a range that ends before it starts', function () {
    $this->getJson('/api/v3/admin/reading-stats?from=2026-10-10&to=2026-10-01', authHeader(makeAdmin()))
        ->assertUnprocessable();
});

/* ------------------------------------------------------------------ 阅读统计 */

it('counts readers by client and compares with the previous period', function () {
    $a = makeStudio('r1-'.Str::random(4));
    $b = makeStudio('r2-'.Str::random(4));
    addRecent($a, '93-6', 'reading', '2026-10-20 08:00:00');   // App
    addRecent($a, '93-9', 'reading', '2026-10-21 08:00:00');
    addRecent($b, '93-6', 'read', '2026-10-21 09:00:00');      // Web
    addRecent($b, '169-3', 'read', '2026-10-10 09:00:00');     // 上一区间（10-08～10-14）
    DB::table('reading_dailies')->insert([
        'user_uid' => $a, 'day' => '2026-10-20', 'seconds' => 1200, 'seconds_reported' => 1500,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $data = $this->getJson('/api/v3/admin/reading-stats?from=2026-10-15&to=2026-10-21', authHeader(makeAdmin()))
        ->assertOk()->json('data');

    expect($data['summary']['active_readers'])->toBe(2)
        ->and($data['summary']['chapters_opened'])->toBe(3)
        ->and($data['summary']['reading_seconds'])->toBe(1200)
        ->and($data['previous']['active_readers'])->toBe(1)
        ->and($data['daily'])->toHaveCount(7)
        ->and(collect($data['daily'])->firstWhere('date', '2026-10-21'))->toMatchArray(['readers_web' => 1, 'readers_mobile' => 1])
        ->and(collect($data['daily'])->firstWhere('date', '2026-10-20')['reading_minutes'])->toBe(20)
        ->and($data['top_chapters'][0])->toMatchArray(['article_id' => '93-6', 'readers' => 2])
        ->and(collect($data['duration_buckets'])->firstWhere('bucket', '15-30')['user_days'])->toBe(1);
});

it('filters reading stats by client', function () {
    $a = makeStudio('c1-'.Str::random(4));
    addRecent($a, '93-6', 'reading', '2026-10-20 08:00:00');
    addRecent(makeStudio('c2-'.Str::random(4)), '93-6', 'read', '2026-10-20 08:00:00');

    $web = $this->getJson('/api/v3/admin/reading-stats?from=2026-10-20&to=2026-10-20&client=web', authHeader(makeAdmin()))
        ->assertOk()->json('data');

    expect($web['summary']['active_readers'])->toBe(1)
        ->and($web['summary']['reading_seconds'])->toBeNull()
        ->and($web['reading_credits'])->toBeNull();
});

it('rejects an unknown client', function () {
    $this->getJson('/api/v3/admin/reading-stats?client=tv', authHeader(makeAdmin()))->assertUnprocessable();
});

/* ------------------------------------------------------------------ AI 对话统计 */

it('summarises agent runs', function () {
    $user = (string) Str::uuid();
    $userThread = AgentThread::factory()->forUser($user)->create();
    $guestThread = AgentThread::factory()->create();
    $at = '2026-10-20 10:00:00';
    AgentRun::factory()->for($userThread, 'thread')->create(['route' => 'term', 'deep' => true, 'latency_ms' => 1000, 'started_at' => $at, 'tokens_in' => 100]);
    AgentRun::factory()->for($userThread, 'thread')->create(['route' => 'passage', 'latency_ms' => 3000, 'started_at' => $at]);
    AgentRun::factory()->for($guestThread, 'thread')->create(['route' => null, 'status' => 'denied', 'latency_ms' => 50, 'started_at' => $at, 'improve' => false]);
    AgentRun::factory()->for($guestThread, 'thread')->create(['route' => 'question', 'status' => 'ok', 'latency_ms' => 99999, 'started_at' => '2026-09-01 00:00:00']);

    $data = $this->getJson('/api/v3/admin/agent-stats?from=2026-10-20&to=2026-10-20', authHeader(makeAdmin()))
        ->assertOk()->json('data');

    expect($data['summary'])->toMatchArray([
        'runs' => 3, 'deep' => 1, 'passage' => 1, 'denied' => 1, 'improve' => 2,
        'askers' => 2, 'askers_user' => 1, 'askers_guest' => 1, 'latency_p50_ms' => 2000, 'tokens_in' => 100,
    ])->and($data['daily'][0])->toMatchArray(['term' => 1, 'passage' => 1, 'none' => 1, 'denied' => 1]);

    $users = $this->getJson('/api/v3/admin/agent-stats?from=2026-10-20&to=2026-10-20&account_type=user', authHeader(makeAdmin()))
        ->json('data.summary.runs');
    expect($users)->toBe(2);
});

it('lists tool calls and credit flows', function () {
    $thread = AgentThread::factory()->create();
    $run = AgentRun::factory()->for($thread, 'thread')->create(['started_at' => '2026-10-20 10:00:00']);
    $run->steps()->createMany([
        ['seq' => 0, 'kind' => 'tool', 'name' => 'wikipali_search', 'ms' => 100],
        ['seq' => 1, 'kind' => 'tool', 'name' => 'wikipali_search', 'ms' => 300, 'error' => 'boom'],
        ['seq' => 2, 'kind' => 'event', 'name' => 'quota', 'args' => ['result' => 'unchecked']],
    ]);
    DB::table('credit_ledger')->insert([
        'account_type' => $thread->account_type, 'account_id' => $thread->account_id, 'delta' => -10, 'kind' => 'spend', 'bucket' => 'free', 'source' => 'agent_run',
        'ref' => $run->run_id, 'day' => '2026-10-20', 'policy_version' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $data = $this->getJson('/api/v3/admin/agent-stats?from=2026-10-20&to=2026-10-20', authHeader(makeAdmin()))->json('data');

    expect($data['tools'][0])->toMatchArray(['name' => 'wikipali_search', 'calls' => 2, 'errors' => 1, 'avg_ms' => 200])
        ->and($data['summary']['unchecked'])->toBe(1)
        ->and($data['credits']['spent_free'])->toBe(10);
});

/* ------------------------------------------------------------------ 提问列表与研究过程 */

it('lists runs newest first with filters', function () {
    $thread = AgentThread::factory()->create();
    AgentRun::factory()->for($thread, 'thread')->create(['question' => '什么是四圣谛？', 'status' => 'ok']);
    AgentRun::factory()->for($thread, 'thread')->create(['question' => 'What is anatta?', 'status' => 'denied']);

    $admin = authHeader(makeAdmin());
    $this->getJson('/api/v3/admin/agent-runs', $admin)
        ->assertOk()
        ->assertJsonPath('data.0.question', 'What is anatta?')
        ->assertJsonPath('data.0.account_type', 'guest')
        ->assertJsonMissingPath('data.0.steps')
        ->assertJsonPath('meta.total', 2);
    $this->getJson('/api/v3/admin/agent-runs?filter[status]=ok', $admin)->assertJsonCount(1, 'data');
    $this->getJson('/api/v3/admin/agent-runs?filter[q]=四圣谛', $admin)->assertJsonCount(1, 'data');
});

it('shows the full research process of a run', function () {
    $user = makeStudio('asker-'.Str::random(4));
    $thread = AgentThread::factory()->forUser($user)->create();
    $run = AgentRun::factory()->for($thread, 'thread')->create(['evidence' => '证据全文']);
    $run->steps()->create(['seq' => 0, 'kind' => 'route', 'args' => ['route' => 'term']]);

    $this->getJson("/api/v3/admin/agent-runs/{$run->run_id}", authHeader(makeAdmin()))
        ->assertOk()
        ->assertJsonPath('data.evidence', '证据全文')
        ->assertJsonPath('data.steps.0.kind', 'route')
        ->assertJsonPath('data.user.id', $user);
});

it('returns 404 for an unknown run', function () {
    $this->getJson('/api/v3/admin/agent-runs/'.Str::uuid(), authHeader(makeAdmin()))->assertNotFound();
});

it('rejects an unknown status filter', function () {
    $this->getJson('/api/v3/admin/agent-runs?filter[status]=weird', authHeader(makeAdmin()))->assertUnprocessable();
});
