<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * editor_id 是 bigint，只装得下人类用户的自增 sn；AI 模型的身份是 uuid，
     * 用模型 token 写入时 editor_id 恒为 0，署名会丢失。补一列 uuid，与
     * sentences.editor_uid 对齐。老数据该列为 null，读取时回落 editor_id。
     */
    public function up(): void
    {
        Schema::table('dhamma_terms', function (Blueprint $table) {
            $table->uuid('editor_uid')->nullable()->index()
                ->comment('编辑者 uuid，人类用户或 AI 模型');
        });
    }

    public function down(): void
    {
        Schema::table('dhamma_terms', function (Blueprint $table) {
            $table->dropIndex(['editor_uid']);
            $table->dropColumn('editor_uid');
        });
    }
};
