<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 一次提问里的每一步：LLM 调用、工具调用，以及路由 / 计划 / 补查 / judge 等节点事件。
 * 结果由 agent 端截断到 8k 再送来，原长记在 result_chars。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_run_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_run_id')->comment('所属提问 agent_runs.id')->constrained('agent_runs');
            $table->unsignedSmallInteger('seq')->comment('本次提问内的步骤序号');
            $table->string('kind', 16)->comment('llm / tool / route / plan / retry / judge / passage / answer …');
            $table->string('name', 128)->nullable()->comment('模型名或工具名');
            $table->jsonb('args')->nullable()->comment('调用参数');
            $table->text('result')->nullable()->comment('调用结果，agent 端截断到 8k');
            $table->unsignedInteger('result_chars')->nullable()->comment('截断前结果的字符数');
            $table->unsignedInteger('tokens_in')->nullable()->comment('本步输入 token 数');
            $table->unsignedInteger('tokens_out')->nullable()->comment('本步输出 token 数');
            $table->unsignedInteger('ms')->nullable()->comment('本步耗时（毫秒）');
            $table->text('error')->nullable()->comment('本步的错误信息');
            $table->timestamp('started_at')->nullable()->comment('本步开始时间');
            $table->softDeletes()->comment('随所属提问一起软删；数据永久保留');

            $table->unique(['agent_run_id', 'seq']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_run_steps');
    }
};
