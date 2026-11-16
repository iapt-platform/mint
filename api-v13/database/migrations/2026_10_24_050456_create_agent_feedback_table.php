<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用户对 AI 回答的评价（点赞 / 点踩）。一人对一条回答只留一条，再点就是更新，可撤回（删除）。
 *
 * 键是 AG-UI 的回答消息 id（客户端手里只有它；研究过程是回答结束后异步入库的，
 * 用户点得快时那次提问可能还没进库）。读的时候按 agent_runs.assistant_message_id 对回提问。
 *
 * tags：点赞是可选标签（accurate / useful_citations / clear / inspiring），
 * 点踩是原因（citation_wrong / missed / off_topic / incorrect / too_long / too_academic / slow / other）。
 * comment 只在选了 other 时填。asked＝这条是主动询问（不是用户自己点）问出来的。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 8)->comment('账号类型：user（登录用户）/ guest（游客）');
            $table->uuid('account_id')->comment('账号 id：登录用户 uuid 或游客设备匿名 uuid；必须与 account_type 一起用（设备 id 由客户端填，可能与某个用户 uid 相同）');
            $table->string('message_id', 64)->index()->comment('AG-UI 回答消息 id');
            $table->uuid('thread_id')->nullable()->comment('AG-UI threadId');
            $table->smallInteger('rating')->comment('1 赞 / -1 踩');
            $table->jsonb('tags')->nullable()->comment('点赞的标签或点踩的原因，取值见类注释');
            $table->text('comment')->nullable()->comment('补充说明，只在选了 other 时填');
            $table->boolean('asked')->default(false)->comment('主动询问问出来的');
            $table->string('client', 16)->nullable()->comment('mobile / web');
            $table->timestamps();

            $table->unique(['account_id', 'account_type', 'message_id']);
            $table->index(['rating', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_feedback');
    }
};
