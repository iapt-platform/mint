<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Models\CreditLedgerEntry;
use App\Models\ReadingDaily;
use App\Models\UserInfo;
use App\Models\UserOperationDaily;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * AI 对话的额度与积分。
 *
 * 两个池子：
 * - **每日赠送池**（bucket=free）：每个 UTC 日按策略送一份，当天用完不累积。赠送本身不入账，
 *   「今天还剩多少」= 赠送额 − 当天 free 流水的消耗；
 * - **积分余额**（bucket=earned）：读经、编辑时长赚来的，加上手工调整，不过期。
 *
 * 每次提问先扣赠送池，不够再扣积分余额；两者合计不够就拒绝（402）。
 * 规则全部来自当前生效的策略 `config('mint.credits.policy')`（公开：`GET /v3/credit-policy`），
 * 字段说明见 agent-poc 仓库 docs/credits.md。
 */
class CreditService
{
    /**
     * 当前生效的策略。version 是生效日（YYYYMMDD），记进每笔流水。
     *
     * @return array{version: int, rules: array<string, mixed>}
     */
    public function policy(): array
    {
        $policy = config('mint.credits.policy');

        return ['version' => (int) $policy['version'], 'rules' => Arr::except($policy, 'version')];
    }

    /**
     * 某个账号此刻的额度概况。登录用户会先把今天（和昨天）的读经 / 编辑时长结算成积分。
     *
     * @return array{account_type: string, day: string, resets_at: string, policy_version: int, daily_free: int, free_used: int, free_remaining: int, earned_balance: int, cost: array{normal: int, deep: int}, earned_today: array{reading: int, editing: int}, earn: array<string, mixed>}
     */
    public function quota(AgentAccount $account): array
    {
        $today = $this->today();
        if ($account->isUser()) {
            $this->settleRecent($account->id);
        }
        $rules = $this->policy()['rules'];
        $dailyFree = (int) ($rules['daily_free'][$account->type] ?? 0);
        $freeUsed = $this->freeUsed($account, $today);

        return [
            'account_type' => $account->type,
            'day' => $today->toDateString(),
            'resets_at' => $today->copy()->addDay()->toIso8601ZuluString(),
            'policy_version' => $this->policy()['version'],
            'daily_free' => $dailyFree,
            'free_used' => $freeUsed,
            'free_remaining' => max(0, $dailyFree - $freeUsed),
            'earned_balance' => $this->earnedBalance($account),
            'cost' => ['normal' => (int) $rules['cost']['normal'], 'deep' => (int) $rules['cost']['deep']],
            'earned_today' => [
                'reading' => $this->earnedOn($account, 'reading', $today),
                'editing' => $this->earnedOn($account, 'editing', $today),
            ],
            'earn' => $account->isUser() ? $rules['earn'] : [],
        ];
    }

    /**
     * 为一次提问扣费（按 run_id 幂等）。先扣赠送池，再扣积分余额。
     *
     * @return array{run_id: string, created: bool, cost: int, charged_free: int, charged_earned: int, free_remaining: int, earned_balance: int, policy_version: int}
     *
     * @throws BusinessException 402：额度不足或游客同 IP 超上限
     */
    public function charge(AgentAccount $account, string $runId, bool $deep, ?string $clientIp): array
    {
        if ($account->isUser()) {
            $this->settleRecent($account->id);
        }
        // 游客的同 IP 每日上限靠 IP：拿不到 IP 就不让问，否则上限形同虚设（伪造设备 id 就能无限领赠送额度）
        if (! $account->isUser() && blank($clientIp)) {
            throw ValidationException::withMessages(['client_ip' => __('messages.agent_client_ip_required')]);
        }
        $today = $this->today();
        ['version' => $version, 'rules' => $rules] = $this->policy();
        $clientIp = $account->isUser() ? null : $clientIp;

        return DB::transaction(function () use ($account, $runId, $deep, $clientIp, $today, $version, $rules) {
            // 同一账号的扣费串行化，避免并发的两次提问都看到「还够」
            DB::select('select pg_advisory_xact_lock(hashtext(?))', [$account->key()]);

            $existing = CreditLedgerEntry::ownedBy($account)
                ->where('source', 'agent_run')->where('ref', $runId)->where('kind', 'spend')->get();
            $dailyFree = (int) ($rules['daily_free'][$account->type] ?? 0);
            if ($existing->isNotEmpty()) {
                return $this->chargeResult($account, $runId, false, $existing, $dailyFree, $today, $version);
            }

            $cost = (int) ($deep ? $rules['cost']['deep'] : $rules['cost']['normal']);
            $freeRemaining = max(0, $dailyFree - $this->freeUsed($account, $today));
            $balance = $this->earnedBalance($account);

            $ipCap = $rules['guest_ip_daily_cap'] ?? null;
            if ($clientIp && $ipCap !== null && $this->ipSpent($clientIp, $today) + $cost > (int) $ipCap) {
                throw $this->exhausted('ip_cap', $cost, $freeRemaining, $balance);
            }
            if ($freeRemaining + $balance < $cost) {
                // 游客读经不给积分，提示登录；登录用户提示读经
                throw $this->exhausted('insufficient', $cost, $freeRemaining, $balance, $account->type);
            }

            $fromFree = min($cost, $freeRemaining);
            $rows = collect(['free' => $fromFree, 'earned' => $cost - $fromFree])
                ->filter(fn (int $amount) => $amount > 0)
                ->map(fn (int $amount, string $bucket) => CreditLedgerEntry::create([
                    ...$account->columns(),
                    'delta' => -$amount,
                    'kind' => 'spend',
                    'bucket' => $bucket,
                    'source' => 'agent_run',
                    'ref' => $runId,
                    'day' => $today->toDateString(),
                    'policy_version' => $version,
                    'client_ip' => $clientIp,
                    'note' => $deep ? 'deep' : null,
                ]))->values();

            return $this->chargeResult($account, $runId, true, $rows, $dailyFree, $today, $version);
        });
    }

    /**
     * 退回某次提问的扣费（服务端出错、回答没生成）。按原消耗逐池退回，day 沿用原消耗那天。
     * 没扣过费就什么都不做；重复退款不会退两次。
     */
    public function refundRun(string $runId): void
    {
        $spends = CreditLedgerEntry::where('source', 'agent_run')->where('ref', $runId)->where('kind', 'spend')->get();
        foreach ($spends as $spend) {
            CreditLedgerEntry::createOrFirst([
                'account_type' => $spend->account_type,
                'account_id' => $spend->account_id,
                'source' => 'agent_run',
                'ref' => $runId,
                'kind' => 'refund',
                'bucket' => $spend->bucket,
            ], [
                'delta' => -$spend->delta,
                'day' => $spend->day->toDateString(),
                'policy_version' => $spend->policy_version,
                'client_ip' => $spend->client_ip,
            ]);
        }
    }

    /**
     * 把某个登录用户某一天的读经、编辑时长结算成积分（可重复执行，结果覆盖）。
     *
     * @return array{reading: int, editing: int}
     */
    public function settle(string $userUid, CarbonInterface $day): array
    {
        ['version' => $version, 'rules' => $rules] = $this->policy();
        $account = AgentAccount::user($userUid);
        $userSn = UserInfo::where('userid', $userUid)->value('id');

        $readingSeconds = (int) ReadingDaily::where('user_uid', $userUid)->where('day', $day->toDateString())->value('seconds');
        $editingMs = $userSn === null ? 0 : (int) UserOperationDaily::where('user_id', $userSn)
            ->whereBetween('date_int', $this->editingWindow($day))
            ->sum('duration');

        $amounts = [
            'reading' => $this->earnAmount($rules['earn']['reading'] ?? null, intdiv($readingSeconds, 60)),
            'editing' => $this->earnAmount($rules['earn']['editing'] ?? null, intdiv($editingMs, 60_000)),
        ];
        foreach ($amounts as $source => $amount) {
            $keys = [...$account->columns(), 'source' => $source, 'ref' => $day->toDateString(), 'kind' => 'earn', 'bucket' => 'earned'];
            if ($amount > 0 || CreditLedgerEntry::where($keys)->exists()) {
                $entry = CreditLedgerEntry::updateOrCreate($keys, [
                    'delta' => $amount,
                    'day' => $day->toDateString(),
                    'policy_version' => $version,
                ]);
                // updated_at＝最后一次结算的时间：数额没变也要记下「结算过了」，
                // 否则当天中途结算的那笔永远被 settleMissing 当成没结清
                if (! $entry->wasRecentlyCreated && ! $entry->wasChanged()) {
                    $entry->touch();
                }
            }
        }

        return $amounts;
    }

    /**
     * 结算某一天所有有活动的登录用户，返回人数。供每日命令使用。
     */
    /**
     * 补齐漏结算的日子：从 $since 到 $until（UTC 日，含两端），找出有活动却没结清的「用户 + 日」重新结算。
     * 结算任务漏跑几天都能补上，工作量只跟漏掉的量有关。
     *
     * 算「没结清」的两种情况（只看至少 1 分钟的活动，不足 1 分钟本来就不给积分）：
     * - 账单里没有那天这个来源的积分；
     * - 有，但最后一次结算早于那天的活动结束——读经按 UTC 日，到次日 0 点；编辑按客户端本地日
     *   （见 editingWindow），最晚到次日 12:00 UTC。之后又攒的时长要补上。
     *
     * 补算用当前生效的策略（历史策略不保留，见 config mint.credits.policy）。
     *
     * @return array{pairs: int, users: int}
     */
    public function settleMissing(CarbonInterface $since, CarbonInterface $until): array
    {
        $since = $since->copy()->startOfDay();
        $until = $until->copy()->startOfDay();

        // 有至少 1 分钟活动的「用户 + 日」，以及那天活动结束的时刻
        $activity = [];
        ReadingDaily::whereBetween('day', [$since->toDateString(), $until->toDateString()])
            ->where('seconds', '>=', 60)
            ->get(['user_uid', 'day'])
            ->each(function (ReadingDaily $row) use (&$activity) {
                $day = $row->day->toDateString();
                $activity["{$row->user_uid}|{$day}"]['reading'] = $row->day->copy()->addDay();
            });
        UserOperationDaily::join('user_infos', 'user_infos.id', '=', 'user_operation_dailies.user_id')
            ->whereBetween('user_operation_dailies.date_int', [$this->editingWindow($since)[0], $this->editingWindow($until)[1]])
            ->selectRaw("user_infos.userid as user_uid, (to_timestamp((user_operation_dailies.date_int + 43200000) / 1000.0) at time zone 'UTC')::date as local_day")
            ->groupByRaw('1, 2')
            ->havingRaw('sum(user_operation_dailies.duration) >= 60000')
            ->get()
            ->each(function ($row) use (&$activity) {
                $day = substr((string) $row->local_day, 0, 10);
                $activity["{$row->user_uid}|{$day}"]['editing'] = now('UTC')->parse($day)->addDay()->addHours(12);
            });
        if ($activity === []) {
            return ['pairs' => 0, 'users' => 0];
        }

        // 已有的积分及其最后结算时间
        $settled = CreditLedgerEntry::where('account_type', 'user')
            ->whereIn('source', ['reading', 'editing'])
            ->where('kind', 'earn')
            ->whereBetween('day', [$since->toDateString(), $until->toDateString()])
            ->get(['account_id', 'source', 'ref', 'updated_at'])
            ->mapWithKeys(fn (CreditLedgerEntry $e) => ["{$e->account_id}|{$e->ref}|{$e->source}" => $e->updated_at]);

        $pairs = 0;
        $users = [];
        foreach ($activity as $key => $sources) {
            $missing = false;
            foreach ($sources as $source => $closesAt) {
                $at = $settled["{$key}|{$source}"] ?? null;
                if ($at === null || $at->lt($closesAt)) {
                    $missing = true;
                }
            }
            if (! $missing) {
                continue;
            }
            [$userUid, $day] = explode('|', $key);
            $this->settle($userUid, now('UTC')->parse($day)->startOfDay());
            $pairs++;
            $users[$userUid] = true;
        }

        return ['pairs' => $pairs, 'users' => count($users)];
    }

    public function settleDay(CarbonInterface $day): int
    {
        $fromReading = ReadingDaily::where('day', $day->toDateString())->distinct()->pluck('user_uid');
        $editorSns = UserOperationDaily::whereBetween('date_int', $this->editingWindow($day))->distinct()->pluck('user_id');
        $fromEditing = UserInfo::whereIn('id', $editorSns)->pluck('userid');

        $users = $fromReading->merge($fromEditing)->unique()->values();
        foreach ($users as $userUid) {
            $this->settle((string) $userUid, $day);
        }

        return $users->count();
    }

    /**
     * 手工加减积分（积分余额池）。ref 由调用方给，同一 ref 只生效一次。
     */
    public function adjust(AgentAccount $account, int $delta, string $ref, ?string $note): CreditLedgerEntry
    {
        return CreditLedgerEntry::createOrFirst([
            ...$account->columns(),
            'source' => 'admin',
            'ref' => $ref,
            'kind' => 'adjust',
            'bucket' => 'earned',
        ], [
            'delta' => $delta,
            'day' => $this->today()->toDateString(),
            'policy_version' => $this->policy()['version'],
            'note' => $note,
        ]);
    }

    /**
     * 今天和昨天都结算一次：昨天深夜的活动在每日命令跑之前也能及时到账。
     */
    private function settleRecent(string $userUid): void
    {
        $today = $this->today();
        $this->settle($userUid, $today);
        $this->settle($userUid, $today->copy()->subDay());
    }

    /**
     * @param  array{credits_per_minute?: int, daily_cap?: int|null}|null  $rule
     */
    private function earnAmount(?array $rule, int $minutes): int
    {
        if (! $rule) {
            return 0;
        }
        $amount = $minutes * (int) ($rule['credits_per_minute'] ?? 0);
        $cap = $rule['daily_cap'] ?? null;

        return $cap === null ? $amount : min($amount, (int) $cap);
    }

    /**
     * user_operation_dailies.date_int 是「客户端本地日的零点」的毫秒时间戳（随客户端时区漂移）。
     * 加 12 小时再落到 UTC 日，就是那个本地日的日期——对 UTC−12 到 UTC+12 都成立，
     * 且每行只落进一天，不会重复计。
     *
     * @return array{0: int, 1: int}
     */
    private function editingWindow(CarbonInterface $day): array
    {
        $start = $day->copy()->startOfDay()->getTimestampMs() - 12 * 3600 * 1000;

        return [$start, $start + 24 * 3600 * 1000 - 1];
    }

    private function today(): CarbonInterface
    {
        return now('UTC')->startOfDay();
    }

    private function freeUsed(AgentAccount $account, CarbonInterface $day): int
    {
        return -(int) CreditLedgerEntry::ownedBy($account)
            ->where('bucket', 'free')->where('day', $day->toDateString())->sum('delta');
    }

    private function earnedBalance(AgentAccount $account): int
    {
        return (int) CreditLedgerEntry::ownedBy($account)->where('bucket', 'earned')->sum('delta');
    }

    private function earnedOn(AgentAccount $account, string $source, CarbonInterface $day): int
    {
        return (int) CreditLedgerEntry::ownedBy($account)->where('source', $source)
            ->where('kind', 'earn')->where('day', $day->toDateString())->sum('delta');
    }

    /**
     * 同一 IP 下所有游客当天的净消耗（退回的不算）。
     */
    private function ipSpent(string $clientIp, CarbonInterface $day): int
    {
        return -(int) CreditLedgerEntry::where('client_ip', $clientIp)->where('day', $day->toDateString())
            ->where('account_type', 'guest')->whereIn('kind', ['spend', 'refund'])->sum('delta');
    }

    /**
     * @param  Collection<int, CreditLedgerEntry>  $rows
     * @return array{run_id: string, created: bool, cost: int, charged_free: int, charged_earned: int, free_remaining: int, earned_balance: int, policy_version: int}
     */
    private function chargeResult(AgentAccount $account, string $runId, bool $created, $rows, int $dailyFree, CarbonInterface $today, int $version): array
    {
        $free = -(int) $rows->where('bucket', 'free')->sum('delta');
        $earned = -(int) $rows->where('bucket', 'earned')->sum('delta');

        return [
            'run_id' => $runId,
            'created' => $created,
            'cost' => $free + $earned,
            'charged_free' => $free,
            'charged_earned' => $earned,
            'free_remaining' => max(0, $dailyFree - $this->freeUsed($account, $today)),
            'earned_balance' => $this->earnedBalance($account),
            'policy_version' => $version,
        ];
    }

    private function exhausted(string $reason, int $cost, int $freeRemaining, int $balance, string $accountType = 'guest'): BusinessException
    {
        $key = $reason === 'insufficient' ? "agent_quota_insufficient_{$accountType}" : "agent_quota_{$reason}";

        return new BusinessException(
            __("messages.{$key}"),
            402,
            'quota-exceeded',
            ['reason' => $reason, 'cost' => $cost, 'free_remaining' => $freeRemaining, 'earned_balance' => $balance],
            'Quota Exceeded',
        );
    }
}
