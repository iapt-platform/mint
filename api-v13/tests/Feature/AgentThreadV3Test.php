<?php

use App\Models\AgentRun;
use App\Models\AgentThread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 me/agent-threads：登录用户的 AI 对话会话历史（web chat 与 mobile 共用）。
 *
 *   GET    /api/v3/me/agent-threads                     列表（filter[archived]）
 *   GET    /api/v3/me/agent-threads/{thread}            详情（带可见的问答轮次）
 *   PATCH  /api/v3/me/agent-threads/{thread}            改标题
 *   DELETE /api/v3/me/agent-threads/{thread}            删除（软删，返回 200 + 资源）
 *   PUT    /api/v3/me/agent-threads/{thread}/archive    存档
 *   DELETE /api/v3/me/agent-threads/{thread}/archive    取消存档
 *
 * 这里断言的是形状而非契约（spectator 尚未安装，见 v3-resource skill 第 3 步）。
 */

/* ------------------------------------------------------------------ 列表 */

it('lists my threads, most recent first', function () {
    $me = (string) Str::uuid();
    $older = AgentThread::factory()->forUser($me)->create(['last_run_at' => now()->subDay()]);
    $newer = AgentThread::factory()->forUser($me)->create(['last_run_at' => now()]);
    AgentThread::factory()->forUser((string) Str::uuid())->create();
    AgentThread::factory()->create();

    $data = $this->getJson('/api/v3/me/agent-threads', authHeader($me))
        ->assertOk()
        ->assertJsonStructure(['data' => [['thread_id', 'title', 'archived_at', 'last_run_at']], 'meta' => ['total']])
        ->json('data');

    expect(array_column($data, 'thread_id'))->toBe([$newer->thread_id, $older->thread_id])
        ->and($data[0])->not->toHaveKey('runs');
});

it('filters threads by archived', function () {
    $me = (string) Str::uuid();
    $archived = AgentThread::factory()->forUser($me)->archived()->create();
    $active = AgentThread::factory()->forUser($me)->create();

    expect($this->getJson('/api/v3/me/agent-threads?filter[archived]=true', authHeader($me))->json('data.*.thread_id'))
        ->toBe([$archived->thread_id])
        ->and($this->getJson('/api/v3/me/agent-threads?filter[archived]=false', authHeader($me))->json('data.*.thread_id'))
        ->toBe([$active->thread_id]);
});

it('does not list deleted threads', function () {
    $me = (string) Str::uuid();
    AgentThread::factory()->forUser($me)->create(['deleted_at' => now()]);

    $this->getJson('/api/v3/me/agent-threads', authHeader($me))->assertOk()->assertJsonCount(0, 'data');
});

it('requires login to list threads', function () {
    $this->getJson('/api/v3/me/agent-threads')->assertUnauthorized();
});

it('rejects an invalid archived filter', function () {
    $this->getJson('/api/v3/me/agent-threads?filter[archived]=maybe', authHeader((string) Str::uuid()))
        ->assertUnprocessable();
});

/* ------------------------------------------------------------------ 详情 */

it('shows a thread with its visible runs in order', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();
    $first = AgentRun::factory()->for($thread, 'thread')->create(['started_at' => now()->subMinutes(10), 'question' => 'Q1']);
    $replaced = AgentRun::factory()->for($thread, 'thread')->create(['started_at' => now()->subMinutes(5), 'question' => 'Q2']);
    AgentRun::factory()->for($thread, 'thread')->create([
        'started_at' => now()->subMinutes(4), 'question' => 'Q2', 'answer' => 'regenerated', 'replaces_run_id' => $replaced->run_id,
    ]);

    $runs = $this->getJson("/api/v3/me/agent-threads/{$thread->thread_id}", authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.thread_id', $thread->thread_id)
        ->json('data.runs');

    expect($runs)->toHaveCount(2)
        ->and($runs[0]['run_id'])->toBe($first->run_id)
        ->and($runs[1]['answer'])->toBe('regenerated')
        ->and($runs[0])->toHaveKeys(['question', 'answer', 'citations', 'deep', 'user_message_id', 'assistant_message_id'])
        ->and($runs[0])->not->toHaveKeys(['evidence', 'plan', 'client_ip', 'improve']);
});

it('hides other users threads as 404', function () {
    $other = AgentThread::factory()->forUser((string) Str::uuid())->create();

    $this->getJson("/api/v3/me/agent-threads/{$other->thread_id}", authHeader((string) Str::uuid()))
        ->assertNotFound();
});

it('does not expose guest threads through the user api', function () {
    $guest = AgentThread::factory()->create();

    $this->getJson("/api/v3/me/agent-threads/{$guest->thread_id}", authHeader((string) Str::uuid()))
        ->assertNotFound();
});

it('returns 404 for a non-uuid thread id', function () {
    $this->getJson('/api/v3/me/agent-threads/not-a-uuid', authHeader((string) Str::uuid()))->assertNotFound();
});

/* ------------------------------------------------------------------ 改名 */

it('renames my thread', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();

    $this->patchJson("/api/v3/me/agent-threads/{$thread->thread_id}", ['title' => '新标题'], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.title', '新标题');
});

it('ignores fields other than title on rename', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();

    $this->patchJson("/api/v3/me/agent-threads/{$thread->thread_id}", ['account_type' => 'guest', 'account_id' => (string) Str::uuid()], authHeader($me))
        ->assertOk();

    expect($thread->fresh()->only('account_type', 'account_id'))->toBe(['account_type' => 'user', 'account_id' => $me]);
});

it('rejects an empty title', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();

    $this->patchJson("/api/v3/me/agent-threads/{$thread->thread_id}", ['title' => ''], authHeader($me))
        ->assertUnprocessable();
});

it('does not register PUT on a thread', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();

    $this->putJson("/api/v3/me/agent-threads/{$thread->thread_id}", ['title' => 'x'], authHeader($me))
        ->assertStatus(405);
});

/* ------------------------------------------------------------------ 存档 */

it('archives and unarchives my thread', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();

    $archivedAt = $this->putJson("/api/v3/me/agent-threads/{$thread->thread_id}/archive", [], authHeader($me))
        ->assertOk()
        ->json('data.archived_at');
    expect($archivedAt)->not->toBeNull();

    // 幂等：再存档一次，时间不变
    $this->putJson("/api/v3/me/agent-threads/{$thread->thread_id}/archive", [], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.archived_at', $archivedAt);

    $this->deleteJson("/api/v3/me/agent-threads/{$thread->thread_id}/archive", [], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.archived_at', null);
});

it('cannot archive another users thread', function () {
    $other = AgentThread::factory()->forUser((string) Str::uuid())->create();

    $this->putJson("/api/v3/me/agent-threads/{$other->thread_id}/archive", [], authHeader((string) Str::uuid()))
        ->assertNotFound();
});

it('requires login to archive', function () {
    $thread = AgentThread::factory()->forUser((string) Str::uuid())->create();

    $this->putJson("/api/v3/me/agent-threads/{$thread->thread_id}/archive")->assertUnauthorized();
});

/* ------------------------------------------------------------------ 删除 */

it('soft deletes my thread together with its runs and steps', function () {
    $me = (string) Str::uuid();
    $thread = AgentThread::factory()->forUser($me)->create();
    $run = AgentRun::factory()->for($thread, 'thread')->create();
    $step = $run->steps()->create(['seq' => 0, 'kind' => 'tool', 'name' => 'wikipali_search']);
    $otherRun = AgentRun::factory()->for(AgentThread::factory()->forUser($me), 'thread')->create();

    $this->deleteJson("/api/v3/me/agent-threads/{$thread->thread_id}", [], authHeader($me))
        ->assertOk()
        ->assertJsonPath('data.thread_id', $thread->thread_id);

    // 软删、不是真删：数据都还在
    $this->assertSoftDeleted($thread);
    $this->assertSoftDeleted($run);
    $this->assertSoftDeleted($step);
    expect(AgentRun::where('agent_thread_id', $thread->id)->count())->toBe(0)
        ->and(AgentRun::withTrashed()->where('agent_thread_id', $thread->id)->count())->toBe(1)
        ->and($otherRun->fresh()->deleted_at)->toBeNull();
    $this->getJson("/api/v3/me/agent-threads/{$thread->thread_id}", authHeader($me))->assertNotFound();
});

it('cannot delete another users thread', function () {
    $other = AgentThread::factory()->forUser((string) Str::uuid())->create();

    $this->deleteJson("/api/v3/me/agent-threads/{$other->thread_id}", [], authHeader((string) Str::uuid()))
        ->assertNotFound();
    expect($other->fresh()->deleted_at)->toBeNull();
});
