<?php

use App\Facades\RedisCache;
use App\Models\CreditLedgerEntry;
use App\Models\ReadingDaily;
use App\Models\UserInfo;
use App\Services\V3\AgentAccount;
use App\Services\V3\CreditAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 管理后台 AI 对话积分管理（仅 root / administrator）：
 *
 *   GET  /api/v3/admin/credit-overview     概览
 *   GET  /api/v3/admin/credit-accounts     账号一览
 *   GET  /api/v3/admin/credit-ledger       流水
 *   POST /api/v3/admin/credit-adjustments  手工加减
 *   POST /api/v3/admin/credit-settlements  手工结算
 *
 * 这里断言的是形状而非契约（spectator 尚未安装，见 v3-resource skill 第 3 步）。
 */
function creditAdmin(): string
{
    $uid = makeStudio('cadmin-'.Str::random(6));
    UserInfo::where('userid', $uid)->update(['role' => json_encode(['administrator'])]);

    return $uid;
}

function ledgerRow(AgentAccount $account, int $delta, array $extra = []): CreditLedgerEntry
{
    return CreditLedgerEntry::create([
        ...$account->columns(), 'delta' => $delta, 'kind' => 'earn', 'bucket' => 'earned', 'source' => 'reading',
        'ref' => (string) Str::uuid(), 'day' => now('UTC')->toDateString(), 'policy_version' => 1, ...$extra,
    ]);
}

/* ------------------------------------------------------------------ 权限 */

it('requires login', function (string $method, string $path) {
    $this->json($method, "/api/v3/admin/{$path}")->assertUnauthorized();
})->with([
    ['GET', 'credit-overview'], ['GET', 'credit-accounts'], ['GET', 'credit-ledger'],
    ['POST', 'credit-adjustments'], ['POST', 'credit-settlements'],
]);

it('forbids non-admin users', function (string $method, string $path) {
    $this->json($method, "/api/v3/admin/{$path}", [], authHeader(makeStudio('plain-'.Str::random(5))))->assertForbidden();
})->with([
    ['GET', 'credit-overview'], ['GET', 'credit-accounts'], ['GET', 'credit-ledger'],
    ['POST', 'credit-adjustments'], ['POST', 'credit-settlements'],
]);

/* ------------------------------------------------------------------ 概览 */

it('summarises balances and recent settlements', function () {
    $user = AgentAccount::user(makeStudio('reader-'.Str::random(5)));
    ledgerRow($user, 40);
    ledgerRow($user, -10, ['kind' => 'spend', 'source' => 'agent_run']);
    ledgerRow(AgentAccount::guest((string) Str::uuid()), -10, ['kind' => 'spend', 'bucket' => 'free', 'source' => 'agent_run']);

    $data = $this->getJson('/api/v3/admin/credit-overview', authHeader(creditAdmin()))->assertOk()->json('data');

    expect($data['totals'])->toMatchArray([
        'user_accounts' => 1, 'guest_accounts' => 1, 'outstanding_balance' => 30, 'earned_total' => 40, 'spent_today' => 20,
    ])
        ->and($data['recent_days'])->toHaveCount(CreditAdminService::RECENT_DAYS)
        ->and($data['recent_days'][0])->toMatchArray(['day' => now('UTC')->toDateString(), 'users' => 1, 'reading' => 40])
        ->and($data['last_settlement'])->toBeNull()
        ->and($data['policy'])->toHaveKeys(['daily_free', 'cost', 'earn']);
});

/* ------------------------------------------------------------------ 账号一览 */

it('lists accounts with their balances, searchable by user name', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    $bob = makeStudio('bob-'.Str::random(5));
    ledgerRow(AgentAccount::user($alice), 50);
    ledgerRow(AgentAccount::user($alice), -10, ['kind' => 'spend', 'source' => 'agent_run']);
    ledgerRow(AgentAccount::user($alice), -30, ['kind' => 'spend', 'bucket' => 'free', 'source' => 'agent_run']);
    ledgerRow(AgentAccount::user($bob), 5);
    $admin = creditAdmin();

    $rows = $this->getJson('/api/v3/admin/credit-accounts?filter[q]=alice', authHeader($admin))->assertOk()->json('data');

    expect($rows)->toHaveCount(1)
        ->and($rows[0])->toMatchArray([
            'account_type' => 'user', 'account_id' => $alice, 'earned_balance' => 40, 'earned_total' => 50,
            'spent_total' => 40, 'free_used_today' => 30,
        ])
        ->and($rows[0]['user']['id'])->toBe($alice);

    expect($this->getJson('/api/v3/admin/credit-accounts', authHeader($admin))->json('meta.total'))->toBe(2);
});

it('never treats a guest device id as the user with the same uuid', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    ledgerRow(AgentAccount::guest($alice), -10, ['kind' => 'spend', 'bucket' => 'free', 'source' => 'agent_run']);

    $rows = $this->getJson('/api/v3/admin/credit-accounts?filter[account_type]=guest', authHeader(creditAdmin()))->json('data');

    expect($rows)->toHaveCount(1)->and($rows[0]['user'])->toBeNull();
});

it('rejects an unknown account type', function () {
    $this->getJson('/api/v3/admin/credit-accounts?filter[account_type]=robot', authHeader(creditAdmin()))->assertUnprocessable();
});

/* ------------------------------------------------------------------ 流水 */

it('lists ledger entries of one account without exposing ids', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    ledgerRow(AgentAccount::user($alice), 7);
    ledgerRow(AgentAccount::guest($alice), -10, ['kind' => 'spend', 'bucket' => 'free', 'source' => 'agent_run']);
    ledgerRow(AgentAccount::user(makeStudio('other-'.Str::random(5))), 3);

    $res = $this->getJson("/api/v3/admin/credit-ledger?filter[account_type]=user&filter[account_id]={$alice}", authHeader(creditAdmin()))
        ->assertOk();

    expect($res->json('data'))->toHaveCount(1)
        ->and($res->json('data.0'))->toMatchArray(['delta' => 7, 'kind' => 'earn', 'source' => 'reading', 'operator' => null])
        ->and($res->json('data.0'))->not->toHaveKey('id');
});

it('requires the account type together with the account id', function () {
    $this->getJson('/api/v3/admin/credit-ledger?filter[account_id]='.Str::uuid(), authHeader(creditAdmin()))
        ->assertUnprocessable()->assertJsonValidationErrors('filter.account_type');
});

it('keeps the ledger query count independent of the number of rows', function () {
    $admin = creditAdmin();
    $count = function () use ($admin) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v3/admin/credit-ledger', authHeader($admin))->assertOk();

        return count(DB::getQueryLog());
    };
    ledgerRow(AgentAccount::user(makeStudio('u-'.Str::random(5))), 1);
    $few = $count();
    foreach (range(1, 8) as $i) {
        ledgerRow(AgentAccount::user(makeStudio("u{$i}-".Str::random(5))), 1);
    }

    expect($count())->toBe($few);
});

/* ------------------------------------------------------------------ 手工加减 */

it('grants credits by username and records the operator', function () {
    $username = 'alice-'.Str::random(5);
    $alice = makeStudio($username);
    $admin = creditAdmin();

    $res = $this->postJson('/api/v3/admin/credit-adjustments', ['user' => $username, 'delta' => 200, 'note' => '补发'], authHeader($admin))
        ->assertCreated();

    expect($res->json('data'))->toMatchArray(['account_id' => $alice, 'delta' => 200, 'kind' => 'adjust', 'source' => 'admin', 'note' => '补发'])
        ->and($res->json('data.operator.id'))->toBe($admin)
        ->and($res->json('meta'))->toBe(['created' => true, 'earned_balance' => 200]);
    expect(CreditLedgerEntry::where('account_id', $alice)->value('operator_uid'))->toBe($admin);
});

it('applies the same ref only once', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    $body = ['user' => $alice, 'delta' => 50, 'note' => 'x', 'ref' => (string) Str::uuid()];
    $admin = authHeader(creditAdmin());

    $this->postJson('/api/v3/admin/credit-adjustments', $body, $admin)->assertCreated();
    $this->postJson('/api/v3/admin/credit-adjustments', $body, $admin)->assertOk()->assertJsonPath('meta.created', false);

    expect((int) CreditLedgerEntry::where('account_id', $alice)->sum('delta'))->toBe(50);
});

it('refuses a deduction that would make the balance negative', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    ledgerRow(AgentAccount::user($alice), 30);

    $this->postJson('/api/v3/admin/credit-adjustments', ['user' => $alice, 'delta' => -31, 'note' => 'x'], authHeader(creditAdmin()))
        ->assertUnprocessable()->assertJsonValidationErrors('delta');
    $this->postJson('/api/v3/admin/credit-adjustments', ['user' => $alice, 'delta' => -30, 'note' => 'x'], authHeader(creditAdmin()))
        ->assertCreated()->assertJsonPath('meta.earned_balance', 0);
});

it('rejects an unknown user, a zero delta and a missing note', function () {
    $admin = authHeader(creditAdmin());

    $this->postJson('/api/v3/admin/credit-adjustments', ['user' => 'nobody-'.Str::random(8), 'delta' => 1, 'note' => 'x'], $admin)
        ->assertUnprocessable()->assertJsonValidationErrors('user');
    $this->postJson('/api/v3/admin/credit-adjustments', ['user' => 'x', 'delta' => 0], $admin)
        ->assertUnprocessable()->assertJsonValidationErrors(['delta', 'note']);
});

/* ------------------------------------------------------------------ 手工结算 */

it('settles missing days, is idempotent and records the last run', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    ReadingDaily::create(['user_uid' => $alice, 'day' => now('UTC')->subDays(2)->toDateString(), 'seconds' => 600, 'seconds_reported' => 600]);
    $admin = creditAdmin();

    $first = $this->postJson('/api/v3/admin/credit-settlements', [], authHeader($admin))->assertCreated()->json('data');
    $this->postJson('/api/v3/admin/credit-settlements', [], authHeader($admin))->assertCreated();

    expect($first)->toMatchArray(['mode' => 'missing', 'users' => 1, 'pairs' => 1, 'trigger' => 'admin'])
        ->and((int) CreditLedgerEntry::where('account_id', $alice)->sum('delta'))->toBe(10)
        ->and(RedisCache::get(CreditAdminService::LAST_SETTLEMENT_KEY)['trigger'])->toBe('admin');
});

it('re-settles one day on demand without double counting', function () {
    $alice = makeStudio('alice-'.Str::random(5));
    $day = now('UTC')->subDay()->toDateString();
    ReadingDaily::create(['user_uid' => $alice, 'day' => $day, 'seconds' => 300, 'seconds_reported' => 300]);
    $admin = authHeader(creditAdmin());

    $this->postJson('/api/v3/admin/credit-settlements', ['date' => $day], $admin)->assertCreated()
        ->assertJsonPath('data.mode', 'day')->assertJsonPath('data.users', 1);
    $this->postJson('/api/v3/admin/credit-settlements', ['date' => $day], $admin)->assertCreated();

    expect((int) CreditLedgerEntry::where('account_id', $alice)->sum('delta'))->toBe(5);
});

it('rejects a settlement date before credits went live or in the future', function (string $date) {
    $this->postJson('/api/v3/admin/credit-settlements', ['date' => $date], authHeader(creditAdmin()))
        ->assertUnprocessable()->assertJsonValidationErrors('date');
})->with([
    'before launch' => ['2020-01-01'],
    'future' => [fn () => now('UTC')->addDay()->toDateString()],
    'malformed' => ['yesterday'],
]);

it('records the daily command as the last run too', function () {
    $this->artisan('credits:settle-activity')->assertSuccessful();

    expect(RedisCache::get(CreditAdminService::LAST_SETTLEMENT_KEY))->toMatchArray(['trigger' => 'cli', 'operator' => null]);
});
