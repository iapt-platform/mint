<?php

namespace App\Http\Requests\V3;

use App\Models\AgentRun;
use App\Models\AgentThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/agent-traces` 的入参：一次提问（AG-UI run）的研究过程，连同全部步骤。
 *
 * 由 agent 服务在一次运行结束后发来。长文本的上限按「一次运行里最多会有多少」
 * 留余量：证据全文给作答 LLM，深度思考时几万字也正常；单步结果 agent 端已截断到 8k。
 */
class StoreAgentTraceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'run_id' => ['required', 'uuid'],
            'thread_id' => ['required', 'uuid'],
            'device_id' => ['nullable', 'uuid'],
            'client_ip' => ['nullable', 'ip'],
            'client' => ['nullable', 'string', Rule::in(AgentThread::CLIENTS)],
            'app_version' => ['nullable', 'string', 'max:32'],
            'locale' => ['nullable', 'string', 'max:16'],

            'question' => ['required', 'string', 'max:20000'],
            'passage' => ['nullable', 'array'],
            'deep' => ['boolean'],
            'improve' => ['boolean'],

            'route' => ['nullable', 'string', 'max:32'],
            'route_raw' => ['nullable', 'string', 'max:32'],
            'confidence' => ['nullable', 'numeric'],
            'plan' => ['nullable', 'array'],
            'evidence' => ['nullable', 'string', 'max:400000'],
            'answer' => ['nullable', 'string', 'max:200000'],
            'citations' => ['nullable', 'array', 'max:200'],

            'model' => ['nullable', 'string', 'max:64'],
            'prompt_version' => ['nullable', 'string', 'max:64'],
            'llm_calls' => ['integer', 'min:0', 'max:65535'],
            'tool_calls' => ['integer', 'min:0', 'max:65535'],
            'retries' => ['integer', 'min:0', 'max:65535'],
            'tokens_in' => ['nullable', 'integer', 'min:0'],
            'tokens_out' => ['nullable', 'integer', 'min:0'],
            'latency_ms' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(AgentRun::STATUSES)],
            'error' => ['nullable', 'string', 'max:20000'],

            'user_message_id' => ['nullable', 'string', 'max:64'],
            'assistant_message_id' => ['nullable', 'string', 'max:64'],
            'replaces_run_id' => ['nullable', 'uuid'],
            // 「重新生成」时被替换那条回答的 AG-UI 消息 id；服务端在同一会话里换算成 replaces_run_id
            'replaces_message_id' => ['nullable', 'string', 'max:64'],
            'started_at' => ['nullable', 'date'],

            'steps' => ['array', 'max:500'],
            'steps.*.seq' => ['required', 'integer', 'min:0', 'max:65535', 'distinct'],
            // 不限定取值：backend 新加的步骤类型照样入库（不认识的记警告），不能因此整条被拒
            'steps.*.kind' => ['required', 'string', 'max:16'],
            'steps.*.name' => ['nullable', 'string', 'max:128'],
            'steps.*.args' => ['nullable', 'array'],
            'steps.*.result' => ['nullable', 'string', 'max:20000'],
            'steps.*.result_chars' => ['nullable', 'integer', 'min:0'],
            'steps.*.tokens_in' => ['nullable', 'integer', 'min:0'],
            'steps.*.tokens_out' => ['nullable', 'integer', 'min:0'],
            'steps.*.ms' => ['nullable', 'integer', 'min:0'],
            'steps.*.error' => ['nullable', 'string', 'max:20000'],
            'steps.*.started_at' => ['nullable', 'date'],
        ];
    }
}
