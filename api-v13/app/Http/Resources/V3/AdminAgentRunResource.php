<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\AgentRun;
use Illuminate\Http\Request;

/**
 * 管理员看到的一次提问。列表里只给摘要；详情（show）另带回答、证据、计划、引用与全部步骤。
 *
 * 游客只有设备匿名 id 与 IP；提问原文对 root / administrator 可见。**本类不查库**：
 * thread、user、steps 由 Service 预加载。
 *
 * @mixin AgentRun
 */
class AdminAgentRunResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     run_id: string,
     *     thread_id: string|null,
     *     thread_title: string|null,
     *     account_type: 'user'|'guest',
     *     user: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     device_id: string|null,
     *     client_ip: string|null,
     *     client: string|null,
     *     app_version: string|null,
     *     question: string,
     *     route: 'question'|'term'|'passage'|'chat'|null,
     *     deep: bool,
     *     improve: bool,
     *     status: string,
     *     latency_ms: int|null,
     *     tokens_in: int|null,
     *     tokens_out: int|null,
     *     llm_calls: int,
     *     tool_calls: int,
     *     started_at: string|null,
     *     answer?: string|null,
     *     evidence?: string|null,
     *     plan?: object|null,
     *     passage?: object|null,
     *     citations?: array<int, object>|null,
     *     error?: string|null,
     *     model?: string|null,
     *     prompt_version?: string|null,
     *     steps?: array<int, array{seq: int, kind: string, name: string|null, args: object|null, result: string|null, result_chars: int|null, tokens_in: int|null, tokens_out: int|null, ms: int|null, error: string|null}>
     * }
     */
    public function toArray($request): array
    {
        $detail = $this->relationLoaded('steps');

        return [
            'run_id' => $this->run_id,
            'thread_id' => $this->thread?->thread_id,
            'thread_title' => $this->thread?->title,
            'account_type' => $this->account_type,
            // 只有登录用户才查得到人；游客的 account_id 是设备 id，不能拿去当用户 uid
            'user' => $this->account_type === 'user' ? $this->actor($this->user) : null,
            'device_id' => $this->account_type === 'guest' ? $this->account_id : null,
            'client_ip' => $this->client_ip,
            'client' => $this->client,
            'app_version' => $this->app_version,
            'question' => $this->question,
            'route' => $this->route,
            'deep' => $this->deep,
            'improve' => $this->improve,
            'status' => $this->status,
            'latency_ms' => $this->latency_ms,
            'tokens_in' => $this->tokens_in,
            'tokens_out' => $this->tokens_out,
            'llm_calls' => $this->llm_calls,
            'tool_calls' => $this->tool_calls,
            'started_at' => $this->started_at,
            $this->mergeWhen($detail, fn () => [
                'answer' => $this->answer,
                'evidence' => $this->evidence,
                'plan' => $this->plan,
                'passage' => $this->passage,
                'citations' => $this->citations,
                'error' => $this->error,
                'model' => $this->model,
                'prompt_version' => $this->prompt_version,
                'steps' => $this->steps->map(fn ($s) => [
                    'seq' => $s->seq,
                    'kind' => $s->kind,
                    'name' => $s->name,
                    'args' => $s->args,
                    'result' => $s->result,
                    'result_chars' => $s->result_chars,
                    'tokens_in' => $s->tokens_in,
                    'tokens_out' => $s->tokens_out,
                    'ms' => $s->ms,
                    'error' => $s->error,
                ])->all(),
            ]),
        ];
    }
}
