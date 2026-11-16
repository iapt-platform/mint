<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI 对话的一次提问（一个 AG-UI run）：问题、路由、计划、给作答 LLM 的证据、回答。
 *
 * 会话历史也从这里读：question / answer / citations 就是用户看得见的那一轮问答，
 * user_message_id / assistant_message_id 是 AG-UI 消息 id，续聊时客户端用它们
 * 把旧消息原样回灌给 agent。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_id')->unique()->comment('AG-UI runId');
            $table->foreignId('agent_thread_id')->comment('所属会话 agent_threads.id')->constrained('agent_threads');
            $table->string('account_type', 8)->comment('提问者账号类型（与所属会话相同，冗余存一份供统计筛选）：user（登录用户）/ guest（游客）');
            $table->uuid('account_id')->comment('账号 id：登录用户 uuid 或游客设备匿名 uuid；必须与 account_type 一起用（设备 id 由客户端填，可能与某个用户 uid 相同）');
            $table->ipAddress('client_ip')->nullable()->comment('客户端 IP，只记游客');
            $table->string('client', 16)->nullable()->comment('mobile / web');
            $table->string('app_version', 32)->nullable()->comment('客户端版本号');
            $table->string('locale', 16)->nullable()->comment('客户端界面语言');

            $table->text('question')->comment('用户提问原文');
            $table->jsonb('passage')->nullable()->comment('阅读页提问带的段落上下文');
            $table->boolean('deep')->default(false)->comment('深度思考');
            $table->boolean('improve')->default(true)->comment('用户是否允许用于改进回答（提问那一刻的开关）');

            $table->string('route', 32)->nullable()->comment('实际走的路由');
            $table->string('route_raw', 32)->nullable()->comment('路由器原始判断');
            $table->float('confidence')->nullable()->comment('路由器判断的置信度');
            $table->jsonb('plan')->nullable()->comment('lemmas / hypothesis 等');
            $table->text('evidence')->nullable()->comment('给作答 LLM 的证据全文');
            $table->text('answer')->nullable()->comment('回答全文');
            $table->jsonb('citations')->nullable()->comment('回答引用的出处列表');

            $table->string('model', 64)->nullable()->comment('作答 LLM 的模型名');
            $table->string('prompt_version', 64)->nullable()->comment('提示词版本哈希');
            $table->unsignedSmallInteger('llm_calls')->default(0)->comment('LLM 调用次数');
            $table->unsignedSmallInteger('tool_calls')->default(0)->comment('工具调用次数');
            $table->unsignedSmallInteger('retries')->default(0)->comment('重试次数');
            $table->unsignedInteger('tokens_in')->nullable()->comment('输入 token 总数');
            $table->unsignedInteger('tokens_out')->nullable()->comment('输出 token 总数');
            $table->unsignedInteger('latency_ms')->nullable()->comment('总耗时（毫秒）');
            $table->string('status', 16)->comment('ok / error / cancelled / denied');
            $table->text('error')->nullable()->comment('出错时的错误信息');

            $table->string('user_message_id', 64)->nullable()->comment('AG-UI 用户消息 id');
            $table->string('assistant_message_id', 64)->nullable()->index()->comment('AG-UI 回答消息 id');
            $table->uuid('replaces_run_id')->nullable()->comment('重新生成时被替换的那一轮');

            $table->timestamp('started_at')->nullable()->index()->comment('开始处理这次提问的时间');
            $table->timestamps();
            $table->softDeletes()->comment('随会话一起软删（用户删除会话）；软删后任何接口都读不到，数据永久保留');

            $table->index(['agent_thread_id', 'created_at']);
            $table->index(['account_id', 'account_type']);
            $table->index(['account_type', 'started_at']);
            $table->index(['client_ip', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_runs');
    }
};
