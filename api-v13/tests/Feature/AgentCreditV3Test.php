<?php

use App\Models\AgentThread;
use App\Models\AiModel;
use App\Models\CreditLedgerEntry;
use App\Models\ReadingDaily;
use App\Models\UserInfo;
use App\Models\UserOperationDaily;
use App\Services\AuthService;
use App\Services\V3\AgentAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 AI 对话额度与积分（策略在 config mint.credits.policy）：
 *
 *   POST /api/v3/agent-charges       提问扣费（服务间，X-Agent-Service-Key）
 *   GET  /api/v3/agent-quota         额度概况（可选登录，游客带 device_id）
 *   POST /api/v3/me/reading-time     读经时长上报（登录）
 *   GET  /api/v3/credit-policy    当前积分策略（公开）
 *   artisan credits:settle-activity / credits:adjust
 *
 * v1：游客每日 100、登录 500；普通 10、深度 30；读经 1/分钟每日上限 100；编辑 5/分钟不设上限；
 * 游客同 IP 每日 500。
 *
 * 这里断言的是形状而非契约（spectator 尚未安装，见 v3-resource skill 第 3 步）。
 */
beforeEach(function () {
    config(['mint.app.agent_service_key' => 'test-agent-key']);
});

function chargeHeaders(?string $userUid = null): array
{
    $headers = ['X-Agent-Service-Key' => 'test-agent-key'];

    return $userUid ? $headers + authHeader($userUid) : $headers;
}

function charge(array $body, ?string $userUid = null, array $headers = [])
{
    // 游客必须带客户端 IP（runtime 写入）；没指定就给一个测试用的
    return test()->postJson('/api/v3/agent-charges', $body + ['run_id' => (string) Str::uuid(), 'client_ip' => '192.0.2.1'], chargeHeaders($userUid) + $headers);
}

function spendFree(AgentAccount $account, int $amount, ?string $ip = null): void
{
    CreditLedgerEntry::create([
        ...$account->columns(), 'delta' => -$amount, 'kind' => 'spend', 'bucket' => 'free',
        'source' => 'agent_run', 'ref' => (string) Str::uuid(), 'day' => now('UTC')->toDateString(),
        'policy_version' => 1, 'client_ip' => $ip,
    ]);
}

/* ------------------------------------------------------------------ 扣费 */

it('refuses a guest without a client ip but not a signed-in user', function () {
    $this->postJson('/api/v3/agent-charges', ['run_id' => (string) Str::uuid(), 'device_id' => (string) Str::uuid()], chargeHeaders())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('client_ip');
    expect(CreditLedgerEntry::count())->toBe(0);

    $this->postJson('/api/v3/agent-charges', ['run_id' => (string) Str::uuid()], chargeHeaders(makeStudio('noip-'.Str::random(5))))
        ->assertCreated();
});

it('refuses to charge for a thread that belongs to another account', function () {
    $thread = AgentThread::factory()->forUser((string) Str::uuid())->create();

    charge(['device_id' => (string) Str::uuid(), 'thread_id' => $thread->thread_id])->assertForbidden();
    expect(CreditLedgerEntry::count())->toBe(0);
});

it('charges for my own thread, deleted or not, and for a new one', function () {
    $device = (string) Str::uuid();
    $thread = AgentThread::factory()->forGuest($device)->create();
    $thread->delete();

    charge(['device_id' => $device, 'thread_id' => $thread->thread_id])->assertCreated();
    charge(['device_id' => $device, 'thread_id' => (string) Str::uuid()])->assertCreated();
});

it('requires the service key to charge', function () {
    $this->postJson('/api/v3/agent-charges', ['run_id' => (string) Str::uuid(), 'device_id' => (string) Str::uuid()])
        ->assertUnauthorized();
});

it('gives a guest ten normal questions a day', function () {
    $device = (string) Str::uuid();

    foreach (range(1, 10) as $i) {
        charge(['device_id' => $device])->assertCreated()->assertJsonPath('data.free_remaining', 100 - 10 * $i);
    }

    charge(['device_id' => $device])
        ->assertStatus(402)
        ->assertJsonPath('type', fn ($type) => str_ends_with($type, 'quota-exceeded'))
        ->assertJsonPath('reason', 'insufficient')
        ->assertJsonPath('cost', 10)
        ->assertJsonPath('free_remaining', 0);
});

it('charges a deep question thirty credits', function () {
    $user = makeStudio('deep-'.Str::random(5));

    charge(['deep' => true], $user)
        ->assertCreated()
        ->assertJsonPath('data.cost', 30)
        ->assertJsonPath('data.charged_free', 30)
        ->assertJsonPath('data.free_remaining', 470);
});

it('is idempotent on run_id', function () {
    $device = (string) Str::uuid();
    $run = (string) Str::uuid();

    charge(['device_id' => $device, 'run_id' => $run])->assertCreated();
    charge(['device_id' => $device, 'run_id' => $run])->assertOk()->assertJsonPath('data.cost', 10);

    expect(CreditLedgerEntry::where('ref', $run)->count())->toBe(1);
});

it('spends daily free first, then earned credits', function () {
    $user = makeStudio('mix-'.Str::random(5));
    spendFree(AgentAccount::user($user), 490);
    $this->artisan('credits:adjust', ['user' => $user, 'delta' => 50, '--reason' => 'test'])->assertSuccessful();

    charge(['deep' => true], $user)
        ->assertCreated()
        ->assertJsonPath('data.charged_free', 10)
        ->assertJsonPath('data.charged_earned', 20)
        ->assertJsonPath('data.free_remaining', 0)
        ->assertJsonPath('data.earned_balance', 30);
});

it('caps all guests behind one ip', function () {
    spendFree(AgentAccount::guest((string) Str::uuid()), 490, '198.51.100.9');

    charge(['device_id' => (string) Str::uuid(), 'client_ip' => '198.51.100.9'])->assertCreated();
    charge(['device_id' => (string) Str::uuid(), 'client_ip' => '198.51.100.9'])
        ->assertStatus(402)
        ->assertJsonPath('reason', 'ip_cap');
    charge(['device_id' => (string) Str::uuid(), 'client_ip' => '198.51.100.10'])->assertCreated();
});

it('does not apply the ip cap to signed-in users', function () {
    spendFree(AgentAccount::guest((string) Str::uuid()), 500, '198.51.100.9');

    charge(['client_ip' => '198.51.100.9'], makeStudio('ipuser-'.Str::random(5)))->assertCreated();
});

it('treats a model token as a guest when charging', function () {
    $human = makeStudio('owner-'.Str::random(5));
    $model = AiModel::factory()->ownedBy($human)->create();

    $this->postJson('/api/v3/agent-charges', ['run_id' => (string) Str::uuid()], [
        'X-Agent-Service-Key' => 'test-agent-key',
        'Authorization' => 'Bearer '.AuthService::getUserToken($model->uid),
    ])->assertUnprocessable()->assertJsonValidationErrors('device_id');
});

/* ------------------------------------------------------------------ 退款 */

it('refunds the charge when the run failed without an answer', function () {
    $device = (string) Str::uuid();
    $run = (string) Str::uuid();
    charge(['device_id' => $device, 'run_id' => $run])->assertCreated();

    $this->postJson('/api/v3/agent-traces', [
        'run_id' => $run, 'thread_id' => (string) Str::uuid(), 'device_id' => $device,
        'question' => 'q', 'status' => 'error', 'error' => 'boom',
    ], chargeHeaders())->assertCreated();

    $this->getJson("/api/v3/agent-quota?device_id={$device}")->assertOk()->assertJsonPath('data.free_remaining', 100);

    // 补发同一条研究过程不会退两次
    $this->postJson('/api/v3/agent-traces', [
        'run_id' => $run, 'thread_id' => (string) Str::uuid(), 'device_id' => $device,
        'question' => 'q', 'status' => 'error',
    ], chargeHeaders())->assertOk();
    expect(CreditLedgerEntry::where('ref', $run)->where('kind', 'refund')->count())->toBe(1);
});

it('keeps the charge when the user cancelled', function () {
    $device = (string) Str::uuid();
    $run = (string) Str::uuid();
    charge(['device_id' => $device, 'run_id' => $run])->assertCreated();

    $this->postJson('/api/v3/agent-traces', [
        'run_id' => $run, 'thread_id' => (string) Str::uuid(), 'device_id' => $device,
        'question' => 'q', 'status' => 'cancelled',
    ], chargeHeaders())->assertCreated();

    $this->getJson("/api/v3/agent-quota?device_id={$device}")->assertJsonPath('data.free_remaining', 90);
});

/* ------------------------------------------------------------------ 额度概况 */

it('shows guest quota without earn rules', function () {
    $this->getJson('/api/v3/agent-quota?device_id='.Str::uuid())
        ->assertOk()
        ->assertJsonPath('data.account_type', 'guest')
        ->assertJsonPath('data.daily_free', 100)
        ->assertJsonPath('data.cost.deep', 30)
        ->assertJsonPath('data.earn', [])
        ->assertJsonStructure(['data' => ['resets_at', 'policy_version', 'earned_balance', 'earned_today']]);
});

it('never mistakes a guest whose device id equals a user uid for that user', function () {
    // 设备 id 由客户端填，可以故意填成别人的 uid：账号必须按 account_type + account_id 两列认
    $user = makeStudio('twin-'.Str::random(5));
    CreditLedgerEntry::create([
        ...AgentAccount::user($user)->columns(), 'delta' => 200, 'kind' => 'adjust', 'bucket' => 'earned',
        'source' => 'admin', 'ref' => 'gift', 'day' => now('UTC')->toDateString(), 'policy_version' => 1,
    ]);
    $userThread = AgentThread::factory()->forUser($user)->create();

    // 看不到他的积分
    $this->getJson("/api/v3/agent-quota?device_id={$user}")
        ->assertOk()
        ->assertJsonPath('data.account_type', 'guest')
        ->assertJsonPath('data.earned_balance', 0);
    // 不能往他的会话里提问
    charge(['device_id' => $user, 'thread_id' => $userThread->thread_id])->assertForbidden();
    // 花掉游客自己的赠送额度后就被拒，碰不到他的积分
    spendFree(AgentAccount::guest($user), 100);
    charge(['device_id' => $user])->assertStatus(402);

    $this->getJson('/api/v3/agent-quota', authHeader($user))
        ->assertJsonPath('data.account_type', 'user')
        ->assertJsonPath('data.earned_balance', 200)
        ->assertJsonPath('data.free_used', 0);
});

it('requires a device id for guest quota', function () {
    $this->getJson('/api/v3/agent-quota')->assertUnprocessable()->assertJsonValidationErrors('device_id');
});

it('shows user quota with earn rules', function () {
    $this->getJson('/api/v3/agent-quota', authHeader(makeStudio('q-'.Str::random(5))))
        ->assertOk()
        ->assertJsonPath('data.account_type', 'user')
        ->assertJsonPath('data.daily_free', 500)
        ->assertJsonPath('data.earn.reading.daily_cap', 100)
        ->assertJsonPath('data.earn.editing.credits_per_minute', 5);
});

/* ------------------------------------------------------------------ 读经时长 */

it('turns reading time into credits', function () {
    $user = makeStudio('reader-'.Str::random(5));

    $this->postJson('/api/v3/me/reading-time', ['seconds' => 300], authHeader($user))
        ->assertOk()
        ->assertJsonPath('data.seconds', 300);

    $this->getJson('/api/v3/agent-quota', authHeader($user))
        ->assertJsonPath('data.earned_today.reading', 5)
        ->assertJsonPath('data.earned_balance', 5);
});

it('clamps reading reports to wall-clock time', function () {
    $user = makeStudio('clamp-'.Str::random(5));

    $this->postJson('/api/v3/me/reading-time', ['seconds' => 600], authHeader($user))->assertOk();
    $this->travel(60)->seconds();
    $this->postJson('/api/v3/me/reading-time', ['seconds' => 600], authHeader($user))
        ->assertOk()
        ->assertJsonPath('data.seconds', 690);
});

it('caps reading credits at one hundred a day', function () {
    $user = makeStudio('cap-'.Str::random(5));
    ReadingDaily::create(['user_uid' => $user, 'day' => now('UTC')->toDateString(), 'seconds' => 20000, 'seconds_reported' => 20000]);

    $this->getJson('/api/v3/agent-quota', authHeader($user))->assertJsonPath('data.earned_today.reading', 100);
});

it('rejects reading reports too far in the past', function () {
    $this->postJson('/api/v3/me/reading-time', [
        'seconds' => 60, 'day' => now('UTC')->subDays(3)->toDateString(),
    ], authHeader(makeStudio('old-'.Str::random(5))))->assertUnprocessable()->assertJsonValidationErrors('day');
});

it('requires login to report reading time', function () {
    $this->postJson('/api/v3/me/reading-time', ['seconds' => 60])->assertUnauthorized();
});

it('rejects an oversized reading report', function () {
    $this->postJson('/api/v3/me/reading-time', ['seconds' => 5000], authHeader(makeStudio('big-'.Str::random(5))))
        ->assertUnprocessable();
});

/* ------------------------------------------------------------------ 编辑时长 */

it('turns editing time into credits without a cap', function () {
    $user = makeStudio('editor-'.Str::random(5));
    $sn = UserInfo::where('userid', $user)->value('id');
    // UTC+8 的客户端：本地今天零点 = UTC 今天零点 − 8 小时
    $localMidnight = now('UTC')->startOfDay()->getTimestampMs() - 8 * 3600 * 1000;
    UserOperationDaily::forceCreate([
        'id' => app('snowflake')->id(), 'user_id' => $sn, 'date_int' => $localMidnight,
        'duration' => 600 * 60 * 1000, 'hit' => 10,
    ]);

    $this->getJson('/api/v3/agent-quota', authHeader($user))->assertJsonPath('data.earned_today.editing', 3000);
});

it('settles yesterday for everyone with the daily command', function () {
    $user = makeStudio('nightly-'.Str::random(5));
    ReadingDaily::create(['user_uid' => $user, 'day' => now('UTC')->subDay()->toDateString(), 'seconds' => 1200, 'seconds_reported' => 1200]);

    $this->artisan('credits:settle-activity')->assertSuccessful();

    expect(CreditLedgerEntry::ownedBy(AgentAccount::user($user))->where('source', 'reading')->value('delta'))->toBe(20);

    // 重复执行不重复入账
    $this->artisan('credits:settle-activity')->assertSuccessful();
    expect(CreditLedgerEntry::ownedBy(AgentAccount::user($user))->where('source', 'reading')->count())->toBe(1);
});

it('backfills every missed day since credits started, however many', function () {
    config(['mint.credits.earn_since' => now('UTC')->subDays(60)->toDateString()]);
    $user = makeStudio('missed-'.Str::random(5));
    $sn = UserInfo::where('userid', $user)->value('id');
    foreach ([10, 40] as $ago) {
        ReadingDaily::create(['user_uid' => $user, 'day' => now('UTC')->subDays($ago)->toDateString(), 'seconds' => 300, 'seconds_reported' => 300]);
    }
    // UTC+8 客户端 25 天前的本地日：编辑 3 分钟
    UserOperationDaily::forceCreate([
        'id' => app('snowflake')->id(), 'user_id' => $sn,
        'date_int' => now('UTC')->subDays(25)->startOfDay()->getTimestampMs() - 8 * 3600 * 1000,
        'duration' => 3 * 60 * 1000, 'hit' => 3,
    ]);
    // 上线日之前的不补
    ReadingDaily::create(['user_uid' => $user, 'day' => now('UTC')->subDays(70)->toDateString(), 'seconds' => 600, 'seconds_reported' => 600]);
    // 不足 1 分钟的不给积分，也不算漏
    ReadingDaily::create(['user_uid' => $user, 'day' => now('UTC')->subDays(5)->toDateString(), 'seconds' => 30, 'seconds_reported' => 30]);

    $this->artisan('credits:settle-activity')->assertSuccessful();

    $earned = CreditLedgerEntry::ownedBy(AgentAccount::user($user))->orderBy('day')->get(['source', 'day', 'delta'])
        ->map(fn ($e) => [$e->source, $e->day->toDateString(), $e->delta])->all();
    expect($earned)->toBe([
        ['reading', now('UTC')->subDays(40)->toDateString(), 5],
        ['editing', now('UTC')->subDays(25)->toDateString(), 15],
        ['reading', now('UTC')->subDays(10)->toDateString(), 5],
    ]);

    // 再跑一次：都已结清，什么都不动
    $this->artisan('credits:settle-activity')->expectsOutputToContain('补结算了 0 位用户')->assertSuccessful();
});

it('re-settles a day that was settled before it ended and leaves closed days alone', function () {
    config(['mint.credits.earn_since' => now('UTC')->subDays(30)->toDateString()]);
    $user = makeStudio('early-'.Str::random(5));
    $early = now('UTC')->subDays(3)->startOfDay();
    $closed = now('UTC')->subDays(4)->startOfDay();
    foreach ([$early, $closed] as $day) {
        ReadingDaily::create(['user_uid' => $user, 'day' => $day->toDateString(), 'seconds' => 1200, 'seconds_reported' => 1200]);
    }
    // 那天中午就结算过（之后又读了），以及那天结束后才结算过（数额以当时为准，不再动）
    $entry = fn ($day, int $delta, $at) => CreditLedgerEntry::create([
        ...AgentAccount::user($user)->columns(), 'delta' => $delta, 'kind' => 'earn', 'bucket' => 'earned', 'source' => 'reading',
        'ref' => $day->toDateString(), 'day' => $day->toDateString(), 'policy_version' => 1,
    ])->forceFill(['updated_at' => $at])->saveQuietly();
    $entry($early, 2, $early->copy()->addHours(12));
    $entry($closed, 7, $closed->copy()->addDay()->addHour());

    $this->artisan('credits:settle-activity')->assertSuccessful();

    $deltas = CreditLedgerEntry::ownedBy(AgentAccount::user($user))->pluck('delta', 'ref');
    expect($deltas[$early->toDateString()])->toBe(20)
        ->and($deltas[$closed->toDateString()])->toBe(7);
});

it('does not keep re-settling an early-settled day whose amount was already right', function () {
    config(['mint.credits.earn_since' => now('UTC')->subDays(30)->toDateString()]);
    $user = makeStudio('same-'.Str::random(5));
    $day = now('UTC')->subDays(3)->startOfDay();
    ReadingDaily::create(['user_uid' => $user, 'day' => $day->toDateString(), 'seconds' => 120, 'seconds_reported' => 120]);
    // 当天中途结算过，数额恰好已经对了（2 分）
    CreditLedgerEntry::create([
        ...AgentAccount::user($user)->columns(), 'delta' => 2, 'kind' => 'earn', 'bucket' => 'earned', 'source' => 'reading',
        'ref' => $day->toDateString(), 'day' => $day->toDateString(), 'policy_version' => 1,
    ])->forceFill(['updated_at' => $day->copy()->addHours(12)])->saveQuietly();

    $this->artisan('credits:settle-activity')->expectsOutputToContain('补结算了 1 位用户')->assertSuccessful();
    $this->artisan('credits:settle-activity')->expectsOutputToContain('补结算了 0 位用户')->assertSuccessful();
});

it('re-settles editing on a western client day settled before that local day ended', function () {
    config(['mint.credits.earn_since' => now('UTC')->subDays(30)->toDateString()]);
    $user = makeStudio('west-'.Str::random(5));
    $sn = UserInfo::where('userid', $user)->value('id');
    $day = now('UTC')->subDays(3)->startOfDay();
    // UTC−8 客户端：本地那天零点 = UTC 当天 08:00，一直到 UTC 次日 08:00
    UserOperationDaily::forceCreate([
        'id' => app('snowflake')->id(), 'user_id' => $sn, 'date_int' => $day->getTimestampMs() + 8 * 3600 * 1000,
        'duration' => 10 * 60 * 1000, 'hit' => 10,
    ]);
    // 次日 01:00 UTC（本地那天还没结束）只结算到 2 分钟
    CreditLedgerEntry::create([
        ...AgentAccount::user($user)->columns(), 'delta' => 10, 'kind' => 'earn', 'bucket' => 'earned', 'source' => 'editing',
        'ref' => $day->toDateString(), 'day' => $day->toDateString(), 'policy_version' => 1,
    ])->forceFill(['updated_at' => $day->copy()->addDay()->addHour()])->saveQuietly();

    $this->artisan('credits:settle-activity')->assertSuccessful();

    expect(CreditLedgerEntry::ownedBy(AgentAccount::user($user))->where('source', 'editing')->value('delta'))->toBe(50);
});

/* ------------------------------------------------------------------ 策略与调整 */

it('shows the current credit policy to anyone', function () {
    $this->getJson('/api/v3/credit-policy')
        ->assertOk()
        ->assertJsonPath('data.version', config('mint.credits.policy.version'))
        ->assertJsonPath('data.day_timezone', 'UTC')
        ->assertJsonPath('data.daily_free.guest', 100)
        ->assertJsonPath('data.cost.deep', 30)
        ->assertJsonPath('data.earn.editing.daily_cap', null);
});

it('charges by the policy in config and records its version', function () {
    config(['mint.credits.policy.version' => 20261201, 'mint.credits.policy.cost.normal' => 5]);

    charge(['device_id' => (string) Str::uuid()])->assertCreated()
        ->assertJsonPath('data.cost', 5)
        ->assertJsonPath('data.policy_version', 20261201);
    expect(CreditLedgerEntry::value('policy_version'))->toBe(20261201);
});

it('applies a manual adjustment only once per ref', function () {
    $user = makeStudio('adj-'.Str::random(5));

    $this->artisan('credits:adjust', ['user' => $user, 'delta' => 100, '--ref' => 'gift-1'])->assertSuccessful();
    $this->artisan('credits:adjust', ['user' => $user, 'delta' => 100, '--ref' => 'gift-1'])->assertSuccessful();

    $this->getJson('/api/v3/agent-quota', authHeader($user))->assertJsonPath('data.earned_balance', 100);
});

it('tells guests to sign in and users to read when out of credits', function () {
    $device = (string) Str::uuid();
    spendFree(AgentAccount::guest($device), 100);
    $user = makeStudio('out-'.Str::random(5));
    spendFree(AgentAccount::user($user), 500);

    $guestDetail = charge(['device_id' => $device], null, ['Accept-Language' => 'zh-Hans'])->assertStatus(402)->json('detail');
    $userDetail = charge([], $user, ['Accept-Language' => 'zh-Hans'])->assertStatus(402)->json('detail');

    expect($guestDetail)->toContain('登录')
        ->and($userDetail)->toContain('读经')
        ->and($guestDetail)->not->toBe('messages.agent_quota_insufficient_guest');
});
