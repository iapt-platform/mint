<?php

namespace App\Services\V3;

use App\Models\AgentRun;
use App\Services\UserService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 管理后台「AI 对话统计」与研究过程查阅。时间范围按 UTC 日，闭区间 [from, to]，
 * 以提问开始时间 started_at 为准（入库时总会填，缺省取入库时刻）。
 *
 * 数据来源：agent_runs（一次提问）、agent_threads（account_type + account_id = 谁问的）、agent_run_steps（工具调用）、
 * credit_ledger（积分流水）。设计见 agent-poc 仓库 docs/trace.md、credits.md。
 *
 * 统计结果缓存 10 分钟；提问列表与详情不缓存。
 */
class AgentStatsService
{
    private const CACHE_SECONDS = 600;

    /**
     * @param  'all'|'user'|'guest'  $accountType
     * @return array<string, mixed>
     */
    public function stats(CarbonImmutable $from, CarbonImmutable $to, string $accountType): array
    {
        $key = "admin:agent-stats:{$from->toDateString()}:{$to->toDateString()}:{$accountType}";

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($from, $to, $accountType) {
            $days = $from->diffInDays($to) + 1;
            $prevTo = $from->subDay();
            $prevFrom = $prevTo->subDays($days - 1);

            return [
                'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'account_type' => $accountType],
                'summary' => $this->summary($from, $to, $accountType),
                'previous' => $this->summary($prevFrom, $prevTo, $accountType),
                'daily' => $this->daily($from, $to, $accountType),
                'clients' => $this->runs($from, $to, $accountType)
                    ->selectRaw("coalesce(r.client, 'unknown') as client, count(*) as runs")->groupByRaw('1')
                    ->orderByDesc('runs')->get()->map(fn ($r) => ['client' => $r->client, 'runs' => (int) $r->runs])->all(),
                'tools' => $this->tools($from, $to, $accountType),
                'feedback_reasons' => $this->feedbackReasons($from, $to, $accountType),
                'credits' => $this->creditFlows($from, $to, $accountType),
            ];
        });
    }

    /**
     * @return array<string, int|float|null>
     */
    private function summary(CarbonImmutable $from, CarbonImmutable $to, string $accountType): array
    {
        $row = $this->runs($from, $to, $accountType)
            ->selectRaw('count(*) as runs')
            ->selectRaw('count(*) filter (where r.deep) as deep')
            ->selectRaw("count(*) filter (where r.route = 'passage') as passage")
            ->selectRaw("count(*) filter (where r.status = 'denied') as denied")
            ->selectRaw("count(*) filter (where r.status = 'error') as errors")
            ->selectRaw("count(*) filter (where r.status = 'cancelled') as cancelled")
            ->selectRaw('count(*) filter (where r.improve) as improve')
            // 账号＝两列一起：用户 uid 与设备 id 可能相同，只数 account_id 会把两人算成一个
            ->selectRaw('count(distinct (t.account_type, t.account_id)) as askers')
            ->selectRaw("count(distinct t.account_id) filter (where t.account_type = 'user') as askers_user")
            ->selectRaw("count(distinct t.account_id) filter (where t.account_type = 'guest') as askers_guest")
            ->selectRaw("percentile_cont(0.5) within group (order by r.latency_ms) filter (where r.status = 'ok') as p50")
            ->selectRaw("percentile_cont(0.95) within group (order by r.latency_ms) filter (where r.status = 'ok') as p95")
            ->selectRaw('coalesce(sum(r.tokens_in), 0) as tokens_in, coalesce(sum(r.tokens_out), 0) as tokens_out')
            ->first();

        $feedback = $this->runs($from, $to, $accountType)
            ->join('agent_feedback as f', 'f.message_id', '=', 'r.assistant_message_id')
            ->selectRaw('count(*) filter (where f.rating > 0) as up, count(*) filter (where f.rating < 0) as down')
            ->selectRaw('count(*) filter (where f.asked) as asked')
            ->first();

        $unchecked = $this->runs($from, $to, $accountType)
            ->join('agent_run_steps as s', fn ($j) => $j->on('s.agent_run_id', '=', 'r.id')->whereNull('s.deleted_at'))
            ->where('s.kind', 'event')->where('s.name', 'quota')
            ->whereRaw("s.args->>'result' = 'unchecked'")
            ->count();

        return [
            'runs' => (int) $row->runs,
            'deep' => (int) $row->deep,
            'passage' => (int) $row->passage,
            'denied' => (int) $row->denied,
            'errors' => (int) $row->errors,
            'cancelled' => (int) $row->cancelled,
            'unchecked' => $unchecked,
            'improve' => (int) $row->improve,
            'askers' => (int) $row->askers,
            'askers_user' => (int) $row->askers_user,
            'askers_guest' => (int) $row->askers_guest,
            'latency_p50_ms' => $row->p50 !== null ? (int) round((float) $row->p50) : null,
            'latency_p95_ms' => $row->p95 !== null ? (int) round((float) $row->p95) : null,
            'tokens_in' => (int) $row->tokens_in,
            'tokens_out' => (int) $row->tokens_out,
            'feedback_up' => (int) $feedback->up,
            'feedback_down' => (int) $feedback->down,
            'feedback_asked' => (int) $feedback->asked,
        ];
    }

    /**
     * 点踩原因分布（区间内提问收到的点踩）。
     *
     * @return list<array{reason: string, count: int}>
     */
    private function feedbackReasons(CarbonImmutable $from, CarbonImmutable $to, string $accountType): array
    {
        return $this->runs($from, $to, $accountType)
            ->join('agent_feedback as f', 'f.message_id', '=', 'r.assistant_message_id')
            ->where('f.rating', '<', 0)
            ->crossJoin(DB::raw("jsonb_array_elements_text(coalesce(f.tags, '[]'::jsonb)) as tag(reason)"))
            ->selectRaw('tag.reason as reason, count(*) as n')
            ->groupBy('tag.reason')->orderByDesc('n')->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'count' => (int) $r->n])->all();
    }

    /**
     * 每天一行：按研究路径的提问数、拦截 / 出错数、耗时分位数。没有数据的日子补 0。
     *
     * @return list<array<string, int|string|null>>
     */
    private function daily(CarbonImmutable $from, CarbonImmutable $to, string $accountType): array
    {
        $byRoute = $this->runs($from, $to, $accountType)
            ->selectRaw("date({$this->time()}) as d, coalesce(r.route, 'none') as route, count(*) as n")
            ->groupByRaw('1, 2')->get();
        $other = $this->runs($from, $to, $accountType)
            ->selectRaw("date({$this->time()}) as d")
            ->selectRaw("count(*) filter (where r.status = 'denied') as denied")
            ->selectRaw("count(*) filter (where r.status = 'error') as errors")
            ->selectRaw("percentile_cont(0.5) within group (order by r.latency_ms) filter (where r.status = 'ok') as p50")
            ->selectRaw("percentile_cont(0.95) within group (order by r.latency_ms) filter (where r.status = 'ok') as p95")
            ->groupByRaw('1')->get()->keyBy(fn ($r) => substr((string) $r->d, 0, 10));
        $votes = $this->runs($from, $to, $accountType)
            ->join('agent_feedback as f', 'f.message_id', '=', 'r.assistant_message_id')
            ->selectRaw("date({$this->time()}) as d, count(*) filter (where f.rating > 0) as up, count(*) filter (where f.rating < 0) as down")
            ->groupByRaw('1')->get()->keyBy(fn ($r) => substr((string) $r->d, 0, 10));

        $rows = [];
        for ($day = $from; $day->lte($to); $day = $day->addDay()) {
            $d = $day->toDateString();
            $rows[$d] = ['date' => $d] + array_fill_keys([...AgentRun::ROUTES, 'none'], 0) + [
                'denied' => (int) ($other[$d]->denied ?? 0),
                'errors' => (int) ($other[$d]->errors ?? 0),
                'latency_p50_ms' => isset($other[$d]) && $other[$d]->p50 !== null ? (int) round((float) $other[$d]->p50) : null,
                'latency_p95_ms' => isset($other[$d]) && $other[$d]->p95 !== null ? (int) round((float) $other[$d]->p95) : null,
                'feedback_up' => (int) ($votes[$d]->up ?? 0),
                'feedback_down' => (int) ($votes[$d]->down ?? 0),
            ];
        }
        foreach ($byRoute as $row) {
            $d = substr((string) $row->d, 0, 10);
            $route = in_array($row->route, AgentRun::ROUTES, true) ? $row->route : 'none';
            if (isset($rows[$d])) {
                $rows[$d][$route] += (int) $row->n;
            }
        }

        return array_values($rows);
    }

    /**
     * @return list<array{name: string, calls: int, errors: int, avg_ms: int}>
     */
    private function tools(CarbonImmutable $from, CarbonImmutable $to, string $accountType): array
    {
        return $this->runs($from, $to, $accountType)
            ->join('agent_run_steps as s', fn ($j) => $j->on('s.agent_run_id', '=', 'r.id')->whereNull('s.deleted_at'))
            ->where('s.kind', 'tool')
            ->selectRaw("coalesce(s.name, '?') as name, count(*) as calls, count(*) filter (where s.error is not null) as errors, coalesce(avg(s.ms), 0) as avg_ms")
            ->groupByRaw('1')->orderByDesc('calls')->limit(10)->get()
            ->map(fn ($r) => ['name' => $r->name, 'calls' => (int) $r->calls, 'errors' => (int) $r->errors, 'avg_ms' => (int) round((float) $r->avg_ms)])
            ->all();
    }

    /**
     * 区间内的积分流水：赠送 / 积分消耗、退回、读经 / 编辑发放、手工调整（正负分开记）。
     *
     * @return array<string, int>
     */
    private function creditFlows(CarbonImmutable $from, CarbonImmutable $to, string $accountType): array
    {
        $row = DB::table('credit_ledger')
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->when($accountType !== 'all', fn ($q) => $q->where('account_type', $accountType))
            ->selectRaw("coalesce(-sum(delta) filter (where kind = 'spend' and bucket = 'free'), 0) as spent_free")
            ->selectRaw("coalesce(-sum(delta) filter (where kind = 'spend' and bucket = 'earned'), 0) as spent_earned")
            ->selectRaw("coalesce(sum(delta) filter (where kind = 'refund'), 0) as refunded")
            ->selectRaw("coalesce(sum(delta) filter (where kind = 'earn' and source = 'reading'), 0) as earned_reading")
            ->selectRaw("coalesce(sum(delta) filter (where kind = 'earn' and source = 'editing'), 0) as earned_editing")
            ->selectRaw("coalesce(sum(delta) filter (where kind = 'adjust'), 0) as adjusted")
            ->first();

        return array_map('intval', (array) $row);
    }

    /**
     * 提问列表（管理员查阅），最新的在前。
     *
     * @param  array{filter?: array{status?: string, route?: string, account_type?: string, client?: string, improve?: bool|string, q?: string}}  $filters
     * @return LengthAwarePaginator<int, AgentRun>
     */
    public function listRuns(array $filters, int $perPage): LengthAwarePaginator
    {
        $f = $filters['filter'] ?? [];

        return AgentRun::query()
            ->with(['thread:id,thread_id,account_type,account_id,title', UserService::eagerLoadActor()[0]])
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['route'] ?? null, fn ($q, $v) => $q->where('route', $v))
            ->when($f['client'] ?? null, fn ($q, $v) => $q->where('client', $v))
            ->when($f['account_type'] ?? null, fn ($q, $v) => $q->where('account_type', $v))
            ->when(isset($f['improve']), fn ($q) => $q->where('improve', filter_var($f['improve'], FILTER_VALIDATE_BOOLEAN)))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where('question', 'ilike', '%'.addcslashes($v, '%_\\').'%'))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 分析用的精简导出：区间内**允许用于改进回答**（improve=true）的提问，最早的在前，最多 $limit 条。
     *
     * 不带任何身份（账号、设备、IP）。每条只给分析要用的：问题、路由与把握度、计划里的检索词、
     * 状态与耗时、工具调用序列（名称 / 耗时 / 结果字数 / 是否出错）、用户评价、隐式信号计数，
     * 以及同一会话里下一次提问隔了几秒（短间隔常是「没答到点子上、换个说法再问」）。
     * `$full` 时再带回答、证据全文与引用——回答质量由 agent 拉去评估，服务端不做。
     *
     * @return array{items: list<array<string, mixed>>, has_more: bool}
     */
    public function digest(CarbonImmutable $from, CarbonImmutable $to, int $limit, bool $full = false): array
    {
        $rows = $this->runs($from, $to, 'all')
            ->where('r.improve', true)
            ->select(['r.id', 'r.run_id', 'r.started_at', 'r.question', 'r.route', 'r.route_raw', 'r.confidence', 'r.deep', 'r.status',
                'r.latency_ms', 'r.plan', 'r.passage', 'r.retries', 'r.llm_calls', 'r.tool_calls', 'r.tokens_in', 'r.tokens_out',
                'r.client', 'r.assistant_message_id'])
            ->when($full, fn ($q) => $q->addSelect(['r.answer', 'r.evidence', 'r.citations']))
            ->selectRaw('extract(epoch from (lead(r.started_at) over (partition by r.agent_thread_id order by r.started_at) - r.started_at)) as next_after')
            ->orderBy('r.started_at')->orderBy('r.id')
            ->limit($limit + 1)
            ->get();
        $hasMore = $rows->count() > $limit;
        $rows = $rows->take($limit);

        $ids = $rows->pluck('id');
        $messages = $rows->pluck('assistant_message_id')->filter()->values();
        $steps = DB::table('agent_run_steps')->whereIn('agent_run_id', $ids)->whereNull('deleted_at')->where('kind', 'tool')
            ->orderBy('seq')->get(['agent_run_id', 'name', 'ms', 'result_chars', 'error'])->groupBy('agent_run_id');
        $feedback = DB::table('agent_feedback')->whereIn('message_id', $messages)
            ->get(['message_id', 'rating', 'tags', 'comment', 'asked'])->groupBy('message_id');
        $events = DB::table('agent_events')->whereIn('message_id', $messages)
            ->selectRaw('message_id, kind, count(*) as n')->groupBy('message_id', 'kind')->get()->groupBy('message_id');

        $items = $rows->map(function ($r) use ($steps, $feedback, $events, $full) {
            $plan = $r->plan ? json_decode($r->plan, true) : [];
            $passage = $r->passage ? json_decode($r->passage, true) : null;

            return [
                'run_id' => $r->run_id,
                'started_at' => $r->started_at,
                'question' => $r->question,
                'route' => $r->route,
                'route_raw' => $r->route_raw,
                'confidence' => $r->confidence === null ? null : round((float) $r->confidence, 2),
                'deep' => (bool) $r->deep,
                'from_reader' => $passage !== null,
                'client' => $r->client,
                'status' => $r->status,
                'latency_ms' => $r->latency_ms === null ? null : (int) $r->latency_ms,
                'lemmas' => $plan['lemmas'] ?? [],
                'keywords' => array_map(fn ($k) => is_array($k) ? trim(($k['lemma'] ?? '').'|'.($k['tags'] ?? ''), '|') : $k, $plan['keywords'] ?? []),
                'retries' => (int) $r->retries,
                'llm_calls' => (int) $r->llm_calls,
                'tokens' => (int) $r->tokens_in + (int) $r->tokens_out,
                'tools' => ($steps[$r->id] ?? collect())->map(fn ($s) => [
                    'name' => $s->name, 'ms' => $s->ms === null ? null : (int) $s->ms,
                    'chars' => $s->result_chars === null ? null : (int) $s->result_chars, 'error' => $s->error !== null,
                ])->values()->all(),
                'feedback' => ($feedback[$r->assistant_message_id] ?? collect())->map(fn ($f) => [
                    'rating' => (int) $f->rating, 'tags' => $f->tags ? json_decode($f->tags, true) : [],
                    'comment' => $f->comment, 'asked' => (bool) $f->asked,
                ])->values()->all(),
                'events' => ($events[$r->assistant_message_id] ?? collect())->mapWithKeys(fn ($e) => [$e->kind => (int) $e->n])->all(),
                'next_question_after_s' => $r->next_after === null ? null : (int) round((float) $r->next_after),
                // 给 agent 评估回答质量用：回答、给作答模型的证据全文、引用
                ...($full ? [
                    'answer' => $r->answer,
                    'evidence' => $r->evidence,
                    'citations' => $r->citations ? json_decode($r->citations, true) : [],
                ] : []),
            ];
        })->values()->all();

        return ['items' => $items, 'has_more' => $hasMore];
    }

    /**
     * 一次提问的完整研究过程（含全部步骤）。
     *
     * @throws ModelNotFoundException
     */
    public function showRun(string $runId): AgentRun
    {
        return AgentRun::with(['thread', 'steps', UserService::eagerLoadActor()[0]])->where('run_id', $runId)->firstOrFail();
    }

    private function runs(CarbonImmutable $from, CarbonImmutable $to, string $accountType): Builder
    {
        // 软删的提问（用户删了会话）不计入任何统计
        return DB::table('agent_runs as r')
            ->whereNull('r.deleted_at')
            ->join('agent_threads as t', 't.id', '=', 'r.agent_thread_id')
            ->whereRaw("{$this->time()} >= ?", [$from->startOfDay()])
            ->whereRaw("{$this->time()} < ?", [$to->addDay()->startOfDay()])
            ->when($accountType !== 'all', fn ($q) => $q->where('r.account_type', $accountType));
    }

    private function time(): string
    {
        return 'r.started_at';
    }
}
