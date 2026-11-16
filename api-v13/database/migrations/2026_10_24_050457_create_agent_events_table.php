<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI 回答上的隐式信号：用户不用评价，客户端自动上报。
 *
 * kind：copy（复制回答）/ citation_open（点开引用）/ regenerate（重新生成）/ ask_shown（主动询问显示过）。
 * 中途停止已记在 agent_runs.status=cancelled；「换个说法再问」由分析时按同一会话的提问间隔推出来，不单独上报。
 * 操作记录性质，按 message_id 挂到回答上，随游客保留期一起清理。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_events', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 8)->comment('账号类型：user（登录用户）/ guest（游客）');
            $table->uuid('account_id')->comment('账号 id：登录用户 uuid 或游客设备匿名 uuid；必须与 account_type 一起用（设备 id 由客户端填，可能与某个用户 uid 相同）');
            $table->string('message_id', 64)->comment('AG-UI 回答消息 id');
            $table->string('kind', 32)->comment('copy / citation_open / regenerate / ask_shown');
            $table->string('client', 16)->nullable()->comment('mobile / web');
            $table->timestamp('created_at')->useCurrent()->comment('上报时间');

            $table->index(['account_id', 'account_type']);
            $table->index(['message_id', 'kind']);
            $table->index(['kind', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_events');
    }
};
