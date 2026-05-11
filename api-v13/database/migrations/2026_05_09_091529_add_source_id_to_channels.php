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
            $table->string('source_id', 36)
                ->nullable()->comment('来源id 程序自动更新用 reprint-转载, ai-AI生成 使用');

            // 添加普通索引
            $table->index('source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropIndex(['source_id']);
            $table->dropColumn('source_id');
        });
    }
};
