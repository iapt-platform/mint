<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 给 discussions 增加协作者 co_editor_uid：人类授权 AI 模型代写时，editor_uid 记人类
     * （对内容负责的人），co_editor_uid 记模型（ai_models.uid）。仿 git 的 Co-Authored-By。
     *
     * nullable：人类自己写的、存量数据、v2 写入的都没有协作者。存量里 AI 写的行
     * editor_uid 直接是模型 uid，不回填。
     */
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->uuid('co_editor_uid')->nullable()->index()
                ->comment('协作者：代写的 AI 模型 uid（ai_models.uid）');
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('co_editor_uid');
        });
    }
};
