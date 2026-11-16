<?php

namespace App\Services\V3;

use App\Models\AgentEvent;
use App\Models\AgentFeedback;
use App\Models\AgentRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * AI 回答的用户评价与隐式信号。
 *
 * 一人对一条回答只留一条评价：登录用户按账号、游客按设备匿名 id（同研究过程入库的口径，
 * 见 {@see AgentAccountService}）。再点就是更新，可撤回。评价挂在 AG-UI 回答消息 id 上，
 * 不要求那次提问已经入库。
 *
 * 评价即同意把这次问答用于改进（agent_runs.improve 置为 true），见 {@see rate()}。
 */
class AgentFeedbackService
{
    public function __construct(private readonly AgentAccountService $accounts) {}

    /**
     * 写入或更新评价。
     *
     * @param  array{message_id: string, thread_id?: string|null, rating: int, tags?: list<string>|null, comment?: string|null, asked?: bool, device_id?: string|null, client?: string|null}  $data
     *
     * @throws ValidationException 标签与评分不配套，或游客没带 device_id
     */
    public function rate(?string $bearerUid, array $data): AgentFeedback
    {
        $account = $this->accounts->resolve($bearerUid, $data['device_id'] ?? null);
        $tags = array_values(array_unique($data['tags'] ?? []));
        $allowed = $data['rating'] > 0 ? AgentFeedback::UP_TAGS : AgentFeedback::DOWN_REASONS;
        if (array_diff($tags, $allowed) !== []) {
            throw ValidationException::withMessages(['tags' => __('messages.agent_feedback_tags')]);
        }
        // 文字说明只跟「其他」走
        $comment = in_array('other', $tags, true) ? (trim((string) ($data['comment'] ?? '')) ?: null) : null;

        $feedback = AgentFeedback::firstOrNew([...$account->columns(), 'message_id' => $data['message_id']]);
        $feedback->fill([
            'thread_id' => $data['thread_id'] ?? $feedback->thread_id,
            'rating' => $data['rating'] > 0 ? 1 : -1,
            'tags' => $tags ?: null,
            'comment' => $comment,
            // 一旦是被主动询问问出来的，就一直算
            'asked' => $feedback->asked || (bool) ($data['asked'] ?? false),
            'client' => $data['client'] ?? $feedback->client,
        ])->save();

        // 评价＝同意把这次问答用于改进：客户端在「用于改进」关着时先弹窗征得同意，
        // 不同意就不让评价。所以这条提问即使当时记的是 improve=false，也改成 true（只改本人自己的提问）
        AgentRun::where('assistant_message_id', $data['message_id'])
            ->where('improve', false)
            ->whereHas('thread', fn ($q) => $q->ownedBy($account))
            ->update(['improve' => true]);

        return $feedback;
    }

    /**
     * 撤回评价。没评价过也算成功（幂等）。
     *
     * @throws ValidationException 游客没带 device_id
     */
    public function retract(?string $bearerUid, string $messageId, ?string $deviceId): void
    {
        $account = $this->accounts->resolve($bearerUid, $deviceId);
        AgentFeedback::ownedBy($account)->where('message_id', $messageId)->delete();
    }

    /**
     * 记一条隐式信号。
     *
     * @param  array{message_id: string, kind: string, device_id?: string|null, client?: string|null}  $data
     */
    public function event(?string $bearerUid, array $data): AgentEvent
    {
        $account = $this->accounts->resolve($bearerUid, $data['device_id'] ?? null);

        return AgentEvent::create([
            ...$account->columns(),
            'message_id' => $data['message_id'],
            'kind' => $data['kind'],
            'client' => $data['client'] ?? null,
        ]);
    }

    /**
     * 管理员审阅：评价列表，带对应提问的摘要（问题、路由、状态、质量指标）。最新的在前。
     *
     * @param  array{filter?: array{rating?: string, tag?: string, has_comment?: bool|string}, from?: string, to?: string}  $filters
     * @return LengthAwarePaginator<int, object>
     */
    public function review(array $filters, int $perPage): LengthAwarePaginator
    {
        $f = $filters['filter'] ?? [];

        return DB::table('agent_feedback as f')
            // 软删的提问读不到：评价还在，但不再带出那次问答
            ->leftJoin('agent_runs as r', fn ($j) => $j->on('r.assistant_message_id', '=', 'f.message_id')->whereNull('r.deleted_at'))
            ->select([
                'f.id', 'f.message_id', 'f.rating', 'f.tags', 'f.comment', 'f.asked', 'f.client', 'f.created_at', 'f.updated_at',
                'f.account_type', 'r.run_id', 'r.question', 'r.route', 'r.status', 'r.deep', 'r.latency_ms',
            ])
            ->when(isset($f['rating']), fn ($q) => $q->where('f.rating', (int) $f['rating'] > 0 ? 1 : -1))
            ->when($f['tag'] ?? null, fn ($q, $tag) => $q->whereRaw('jsonb_exists(f.tags, ?)', [$tag]))
            ->when(isset($f['has_comment']) && filter_var($f['has_comment'], FILTER_VALIDATE_BOOLEAN), fn ($q) => $q->whereNotNull('f.comment'))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->where('f.updated_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->where('f.updated_at', '<', now('UTC')->parse($d)->addDay()))
            ->orderByDesc('f.updated_at')
            ->orderByDesc('f.id')
            ->paginate($perPage)
            ->through(function ($row) {
                $row->tags = $row->tags ? json_decode($row->tags, true) : [];

                return $row;
            });
    }
}
