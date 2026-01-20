<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->comment('UUID唯一标识');
            $table->uuid('chat_id')->comment('关联chats.uid');
            $table->uuid('parent_id')->nullable()->comment('关联chat_messages.uid');
            $table->uuid('session_id')->comment('会话段ID');

            $table->enum('role', ['system', 'user', 'assistant', 'tool'])->comment('消息角色');
            $table->text('content')->nullable()->comment('消息内容');
            $table->uuid('model_id')->nullable()->comment('使用的模型UUID');
            $table->json('tool_calls')->nullable()->comment('函数调用信息');
            $table->string('tool_call_id', 100)->nullable()->comment('工具调用ID');

            $table->json('metadata')->nullable()->comment('元数据信息(temperature, tokens等)');
            $table->uuid('editor_id')->nullable()->comment('编辑者UUID');

            $table->boolean('is_active')->default(true)->comment('是否为当前激活版本');
            $table->timestamps();
            $table->softDeletes(); // deleted_at


            $table->index('uid');
            $table->index('chat_id');
            $table->index('parent_id');
            $table->index('session_id');
            $table->index(['chat_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
