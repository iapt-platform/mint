<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * discussions 改为软删除（v3 评论线程重构，2026-10-15 用户同意）。
     *
     * 删除话题时保留这一行做「墓碑」：内容不再返回，回复原样保留，
     * 与 Google Drive 的 comments.deleted、GitHub 的 deletedAt、Discourse 的软删一致。
     * 通知里存着讨论 id，行还在，链接就还能解析。
     */
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->softDeletes()->index();
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
