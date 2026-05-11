<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->string('source_type', 16)
                ->default('original')->comment('来源类型: original-原创, reprint-转载, ai-AI生成');

            // 添加普通索引
            $table->index('source_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            // 先删除索引再删除字段
            $table->dropIndex(['source_type']);
            $table->dropColumn('source_type');
        });
    }
};
