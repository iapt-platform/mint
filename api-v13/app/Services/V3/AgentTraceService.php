<?php

namespace App\Services\V3;

use App\Http\Requests\V3\StoreAgentTraceRequest;
use App\Models\AgentFeedback;
use App\Models\AgentRun;
use App\Models\AgentRunStep;
use App\Models\AgentThread;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * AI 对话的研究过程入库与清理。
 *
 * 写入方是 agent 服务（服务密钥鉴权），终端用户由它透传的 bearer 决定：
 * 有效的人类 token → 记到账号下；没有 / 失效 / 是模型 token → 记为游客，
 * 只认设备匿名 id，并记客户端 IP。token 失效也照记不拒——这批记录要用来防滥用，
 * 不能因为用户登录过期就丢。
 */
class AgentTraceService
{
    /**
     * 会话标题取第一句提问的前多少个字。
     */
    private const TITLE_LENGTH = 60;

    public function __construct(
        private readonly AgentAccountService $accounts,
        private readonly CreditService $credits,
    ) {}

    /**
     * 写入一次提问（幂等）。
     *
     * run_id 已存在时原样返回已有的那条（`wasRecentlyCreated` 为 false → 200），
     * agent 端补发同一条不会写出第二份。status=error 且没有回答时退回这次的扣费。
     *
     * @param  array<string, mixed>  $data  {@see StoreAgentTraceRequest} 校验过的入参
     *
     * @throws ValidationException 游客没带 device_id
     */
    public function record(?string $bearerUid, array $data): AgentRun
    {
        $account = $this->accounts->resolve($bearerUid, $data['device_id'] ?? null);
        $startedAt = isset($data['started_at']) ? Carbon::parse($data['started_at']) : now();

        return DB::transaction(function () use ($data, $account, $startedAt) {
            // 已删除（软删）的提问也算存在：补发同一条不会重建出一份来
            $existing = AgentRun::withTrashed()->with('thread')->where('run_id', $data['run_id'])->first();
            if ($existing) {
                return $existing;
            }

            $thread = AgentThread::withTrashed()->createOrFirst(
                ['thread_id' => $data['thread_id']],
                [
                    ...$account->columns(),
                    'title' => $this->titleOf($data['question']),
                    'client' => $data['client'] ?? null,
                    'last_run_at' => $startedAt,
                ],
            );
            // threadId 全局唯一：别的账号的会话不准往里写（扣费时已拦过，这里兜底）
            if (! $thread->isOwnedBy($account)) {
                abort(403, __('messages.agent_thread_forbidden'));
            }
            if ($thread->last_run_at === null || $thread->last_run_at->lt($startedAt)) {
                $thread->last_run_at = $startedAt;
                $thread->save();
            }

            // 评价先于研究过程到达时：评价了就是同意用于改进（客户端评价前已征得同意）
            if (! ($data['improve'] ?? true) && ! empty($data['assistant_message_id'])
                && AgentFeedback::ownedBy($account)->where('message_id', $data['assistant_message_id'])->exists()) {
                $data['improve'] = true;
            }

            // 重新生成：客户端只知道被替换那条回答的消息 id，在同一会话里换算成那一轮的 run_id。
            // 会话历史回放时被替换的那一轮不再返回（AgentRun::scopeVisible）
            if (empty($data['replaces_run_id']) && ! empty($data['replaces_message_id'])) {
                $data['replaces_run_id'] = AgentRun::where('agent_thread_id', $thread->id)
                    ->where('assistant_message_id', $data['replaces_message_id'])
                    ->value('run_id');
            }

            $run = AgentRun::withTrashed()->createOrFirst(['run_id' => $data['run_id']], [
                ...Arr::except($data, ['run_id', 'thread_id', 'device_id', 'client_ip', 'steps', 'replaces_message_id']),
                'agent_thread_id' => $thread->id,
                ...$account->columns(),
                'client_ip' => $account->isUser() ? null : ($data['client_ip'] ?? null),
                'started_at' => $startedAt,
            ]);

            if ($run->wasRecentlyCreated) {
                $this->warnUnknown($run->run_id, $data);
            }
            if ($run->wasRecentlyCreated && ! empty($data['steps'])) {
                $run->steps()->createMany($data['steps']);
            }
            // 服务端出错、回答没生成 → 退回这次的扣费。用户自己中途取消（cancelled）照扣
            if ($run->wasRecentlyCreated && $run->status === 'error' && blank($run->answer)) {
                $this->credits->refundRun($run->run_id);
            }

            return $run->setRelation('thread', $thread);
        });
    }

    /**
     * backend 送来了 mint 还不认识的路由或步骤类型：照样入库，记一条警告提醒同步枚举
     * （AgentRun::ROUTES / AgentRunStep::KINDS，以及 OpenAPI 与前端）。
     *
     * @param  array<string, mixed>  $data
     */
    private function warnUnknown(string $runId, array $data): void
    {
        $route = $data['route'] ?? null;
        if ($route !== null && ! in_array($route, AgentRun::ROUTES, true)) {
            Log::warning('agent trace: unknown route', ['run_id' => $runId, 'route' => $route]);
        }
        $kinds = array_diff(array_unique(array_column($data['steps'] ?? [], 'kind')), AgentRunStep::KINDS);
        if ($kinds !== []) {
            Log::warning('agent trace: unknown step kinds', ['run_id' => $runId, 'kinds' => array_values($kinds)]);
        }
    }

    private function titleOf(string $question): string
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', $question));

        return mb_strlen($flat) > self::TITLE_LENGTH
            ? mb_substr($flat, 0, self::TITLE_LENGTH).'…'
            : $flat;
    }
}
