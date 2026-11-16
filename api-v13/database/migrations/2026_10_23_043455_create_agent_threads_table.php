<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI 对话的会话（一个 AG-UI thread）。研究过程入库与会话历史共用，设计见
 * agent-poc 仓库 docs/trace.md。
 *
 * 归属用 account_type（user / guest）+ account_id（用户 uuid / 设备匿名 uuid）两列表达，查询时两列一起用。
 * thread_id 由客户端生成、
 * 全局唯一：已属于别的账号的 id 再来一律 403。游客登录 / 退出后客户端换新的 threadId，
 * 游客会话不并入账号。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_threads', function (Blueprint $table) {
            $table->id();
            $table->uuid('thread_id')->unique()->comment('AG-UI threadId，客户端生成；全局唯一，被别的账号占用时拒绝（403）');
            $table->string('account_type', 8)->comment('账号类型：user（登录用户）/ guest（游客）');
            $table->uuid('account_id')->comment('账号 id：登录用户 uuid 或游客设备匿名 uuid；必须与 account_type 一起用（设备 id 由客户端填，可能与某个用户 uid 相同）');
            $table->string('title')->comment('会话标题，默认取第一句提问');
            $table->string('client', 16)->nullable()->comment('mobile / web');
            $table->timestamp('archived_at')->nullable()->comment('用户存档时间');
            $table->timestamp('last_run_at')->index()->comment('最近一次提问时间，游客按它过期');
            $table->timestamps();
            $table->softDeletes()->comment('用户删除＝对用户隐藏（提问、步骤随之软删），数据永久保留');

            $table->index(['account_id', 'account_type', 'last_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_threads');
    }
};
