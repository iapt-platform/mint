<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 术语迁 v3 要的列。v2 / v4 不读这些列；全部可空或带默认值（铁律 4）。
     */
    public function up(): void
    {
        Schema::table('dhamma_terms', function (Blueprint $table) {
            $table->uuid('co_editor_uid')->nullable()
                ->comment('AI 代写时的模型 uid（editor_uid 是授权人）；人亲自改内容时清空');
            $table->string('quality', 16)->nullable()
                ->comment('词条质量：featured / standard / draft / pending；为空按 pending 显示。取代正文里的 {{quality|…}}');
            $table->text('summary')->nullable()
                ->comment('摘要，格式与 note 相同（content_type 决定方言）；渲染时放在正文前。为空时由正文第一段推出');
            $table->string('content_type', 16)->default('markdown')
                ->comment('summary 与 note 的方言：markdown（旧方言，v2 写入的都是它）/ markdown-e2。库默认 markdown 是因为 v2 写入不设这一列；v3 新建默认 markdown-e2');
            $table->unsignedInteger('version')->default(1)
                ->comment('乐观锁版本号，v3 每次修改 +1；ETag 由它与 modify_time 组成（v2 修改只动 modify_time）');
        });
    }

    public function down(): void
    {
        Schema::table('dhamma_terms', function (Blueprint $table) {
            $table->dropColumn(['co_editor_uid', 'quality', 'summary', 'content_type', 'version']);
        });
    }
};
