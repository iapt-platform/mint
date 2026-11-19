<?php

namespace App\Services\V3;

use App\Facades\RedisCache;
use App\Models\CreditLedgerEntry;
use App\Models\UserInfo;
use App\Services\UserService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * 管理后台的 AI 对话积分管理：账号余额一览、流水、手工加减、手工触发结算。
 *
 * 余额口径与 {@see CreditService} 一致：积分余额 = bucket=earned 的流水合计；
 * 每日赠送池不入账，只看当天 free 流水的消耗。
 */
class CreditAdminService
{
    /** 最近一次结算的记录（命令行与管理后台都写），只用于显示，丢了不影响结算本身。 */
    public const LAST_SETTLEMENT_KEY = '/credits/settlement/last';

    /** 概览里按日列出最近多少天的结算结果。 */
    public const RECENT_DAYS = 14;

    public function __construct(private readonly CreditService $credits) {}

    /**
     * 概览：策略、全站合计、最近 14 天每天结算出的积分、最近一次结算任务。
     *
     * @return array{today: string, earn_since: string, policy_version: int, policy: array<string, mixed>, totals: array{user_accounts: int, guest_accounts: int, outstanding_balance: int, earned_total: int, adjusted_total: int, spent_today: int}, recent_days: list<array{day: string, users: int, reading: int, editing: int}>, last_settlement: array<string, mixed>|null}
     */
    public function overview(): array
    {
        ['version' => $version, 'rules' => $rules] = $this->credits->policy();
        $today = now('UTC')->startOfDay();
        $since = $today->copy()->subDays(self::RECENT_DAYS - 1);

        $accounts = CreditLedgerEntry::query()
            ->selectRaw('account_type, count(distinct account_id) as n')
            ->groupBy('account_type')
            ->pluck('n', 'account_type');
        $byKind = CreditLedgerEntry::query()
            ->where('account_type', 'user')->where('bucket', 'earned')
            ->selectRaw('kind, sum(delta) as total')
            ->groupBy('kind')
            ->pluck('total', 'kind');
        $spentToday = -(int) CreditLedgerEntry::query()
            ->where('day', $today->toDateString())->whereIn('kind', ['spend', 'refund'])
            ->sum('delta');

        $earned = CreditLedgerEntry::query()
            ->where('kind', 'earn')->whereBetween('day', [$since->toDateString(), $today->toDateString()])
            ->selectRaw('day, source, count(distinct account_id) as users, sum(delta) as total')
            ->groupBy('day', 'source')
            ->get();
        // 一个人同一天可能读经、编辑都有，人数要按天单独去重
        $usersByDay = CreditLedgerEntry::query()
            ->where('kind', 'earn')->where('delta', '>', 0)
            ->whereBetween('day', [$since->toDateString(), $today->toDateString()])
            ->selectRaw('day, count(distinct account_id) as users')
            ->groupBy('day')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->day->toDateString() => (int) $row->users]);
        $recent = [];
        for ($day = $today->copy(); $day->gte($since); $day = $day->copy()->subDay()) {
            $date = $day->toDateString();
            $rows = $earned->filter(fn ($row) => $row->day->toDateString() === $date);
            $recent[] = [
                'day' => $date,
                'users' => $usersByDay[$date] ?? 0,
                'reading' => (int) $rows->where('source', 'reading')->sum('total'),
                'editing' => (int) $rows->where('source', 'editing')->sum('total'),
            ];
        }

        return [
            'today' => $today->toDateString(),
            'earn_since' => (string) config('mint.credits.earn_since'),
            'policy_version' => $version,
            'policy' => $rules,
            'totals' => [
                'user_accounts' => (int) ($accounts['user'] ?? 0),
                'guest_accounts' => (int) ($accounts['guest'] ?? 0),
                'outstanding_balance' => (int) $byKind->sum(),
                'earned_total' => (int) ($byKind['earn'] ?? 0),
                'adjusted_total' => (int) ($byKind['adjust'] ?? 0),
                'spent_today' => $spentToday,
            ],
            'recent_days' => $recent,
            'last_settlement' => RedisCache::get(self::LAST_SETTLEMENT_KEY),
        ];
    }

    /**
     * 有积分流水的账号，最近有动静的在前。每行是一个账号的汇总（不是模型行）。
     *
     * @param  array{filter?: array{account_type?: string, q?: string}}  $filters
     * @return LengthAwarePaginator<int, CreditLedgerEntry>
     */
    public function accounts(array $filters, int $perPage): LengthAwarePaginator
    {
        $f = $filters['filter'] ?? [];
        $today = now('UTC')->toDateString();

        return CreditLedgerEntry::query()
            ->with(UserService::eagerLoadActor()[0])
            ->select(['account_type', 'account_id'])
            ->selectRaw("sum(case when bucket = 'earned' then delta else 0 end) as earned_balance")
            ->selectRaw("sum(case when kind = 'earn' then delta else 0 end) as earned_total")
            ->selectRaw("sum(case when kind = 'adjust' then delta else 0 end) as adjusted_total")
            ->selectRaw("-sum(case when kind in ('spend', 'refund') then delta else 0 end) as spent_total")
            ->selectRaw("-sum(case when bucket = 'free' and day = ? then delta else 0 end) as free_used_today", [$today])
            ->selectRaw('max(updated_at) as last_activity_at')
            ->when($f['account_type'] ?? null, fn ($q, $v) => $q->where('account_type', $v))
            ->when($f['q'] ?? null, function ($q, string $v) {
                $like = '%'.addcslashes($v, '%_\\').'%';
                $users = UserInfo::query()
                    ->where(fn ($u) => $u->where('username', 'ilike', $like)
                        ->orWhere('nickname', 'ilike', $like)
                        ->orWhere('email', 'ilike', $like))
                    ->limit(200)
                    ->pluck('userid');
                $q->where(fn ($w) => $w
                    ->where(fn ($u) => $u->where('account_type', 'user')->whereIn('account_id', $users))
                    ->orWhere(fn ($g) => $g->where('account_type', 'guest')->where('account_id', 'ilike', addcslashes($v, '%_\\').'%')));
            })
            ->groupBy('account_type', 'account_id')
            ->orderByDesc('last_activity_at')
            ->orderBy('account_id')
            ->paginate($perPage);
    }

    /**
     * 积分流水，最新的在前。
     *
     * @param  array{filter?: array{account_type?: string, account_id?: string, kind?: string, bucket?: string, source?: string, from?: string, to?: string}}  $filters
     * @return LengthAwarePaginator<int, CreditLedgerEntry>
     */
    public function ledger(array $filters, int $perPage): LengthAwarePaginator
    {
        $f = $filters['filter'] ?? [];

        return CreditLedgerEntry::query()
            ->with([UserService::eagerLoadActor()[0], UserService::eagerLoadActor('operator')[0]])
            ->when($f['account_type'] ?? null, fn ($q, $v) => $q->where('account_type', $v))
            ->when($f['account_id'] ?? null, fn ($q, $v) => $q->where('account_id', $v))
            ->when($f['kind'] ?? null, fn ($q, $v) => $q->where('kind', $v))
            ->when($f['bucket'] ?? null, fn ($q, $v) => $q->where('bucket', $v))
            ->when($f['source'] ?? null, fn ($q, $v) => $q->where('source', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->where('day', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->where('day', '<=', $v))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 管理员给某个登录用户加减积分（积分余额池）。同一 ref 只生效一次：重复提交返回原来那条，created=false。
     *
     * @param  string  $user  用户 uuid、用户名或邮箱
     * @return array{entry: CreditLedgerEntry, created: bool, earned_balance: int}
     *
     * @throws ValidationException 用户不存在；扣减后余额为负
     */
    public function adjust(string $operatorUid, string $user, int $delta, string $note, ?string $ref): array
    {
        $userUid = UserInfo::query()
            ->when(Str::isUuid($user),
                fn ($q) => $q->where('userid', $user),
                fn ($q) => $q->where('username', $user)->orWhereRaw('lower(email) = lower(?)', [$user]))
            ->value('userid');
        if ($userUid === null) {
            throw ValidationException::withMessages(['user' => __('messages.credit_user_not_found')]);
        }
        $account = AgentAccount::user($userUid);
        $ref ??= (string) Str::uuid();

        return DB::transaction(function () use ($account, $operatorUid, $delta, $note, $ref) {
            // 与扣费同一把锁：扣减时的余额检查不能和并发的提问扣费交错
            DB::select('select pg_advisory_xact_lock(hashtext(?))', [$account->key()]);

            $balance = (int) CreditLedgerEntry::ownedBy($account)->where('bucket', 'earned')->sum('delta');
            $existing = CreditLedgerEntry::ownedBy($account)
                ->where(['source' => 'admin', 'ref' => $ref, 'kind' => 'adjust', 'bucket' => 'earned'])->first();
            if ($existing === null && $balance + $delta < 0) {
                throw ValidationException::withMessages(['delta' => __('messages.credit_balance_negative', ['balance' => $balance])]);
            }
            $entry = $existing ?? $this->credits->adjust($account, $delta, $ref, $note, $operatorUid);

            return [
                'entry' => $entry->load([UserService::eagerLoadActor()[0], UserService::eagerLoadActor('operator')[0]]),
                'created' => $existing === null,
                'earned_balance' => $existing === null ? $balance + $delta : $balance,
            ];
        });
    }

    /**
     * 手工跑一次结算，与每日任务 `credits:settle-activity` 同一套逻辑，可重复执行、不会重复入账。
     *
     * - 给 $day：只重算那一天的所有用户（结果覆盖那天的积分）；
     * - 不给：从积分上线日到昨天，补齐所有漏结算的「用户 + 日」。
     *
     * @param  string|null  $operatorUid  管理后台触发时的管理员；命令行（每日任务）为 null
     * @return array{mode: string, from: string, to: string, users: int, pairs: int|null, trigger: string, operator: string|null, ran_at: string, seconds: float}
     */
    public function settle(?CarbonInterface $day, ?string $operatorUid = null): array
    {
        $started = microtime(true);
        if ($day !== null) {
            $day = $day->copy()->startOfDay();
            $users = $this->credits->settleDay($day);
            $result = ['mode' => 'day', 'from' => $day->toDateString(), 'to' => $day->toDateString(), 'users' => $users, 'pairs' => null];
        } else {
            $since = now('UTC')->parse((string) config('mint.credits.earn_since'))->startOfDay();
            $until = now('UTC')->subDay()->startOfDay();
            ['pairs' => $pairs, 'users' => $users] = $this->credits->settleMissing($since, $until);
            $result = ['mode' => 'missing', 'from' => $since->toDateString(), 'to' => $until->toDateString(), 'users' => $users, 'pairs' => $pairs];
        }

        $result += [
            'trigger' => $operatorUid === null ? 'cli' : 'admin',
            'operator' => $operatorUid === null ? null : UserInfo::where('userid', $operatorUid)->value('nickname'),
            'ran_at' => now('UTC')->toIso8601ZuluString(),
            'seconds' => round(microtime(true) - $started, 2),
        ];
        RedisCache::put(self::LAST_SETTLEMENT_KEY, $result, now()->addDays(30));

        return $result;
    }
}
