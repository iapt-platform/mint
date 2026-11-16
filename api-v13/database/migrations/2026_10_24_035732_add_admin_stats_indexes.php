<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 管理后台统计（/v3/admin/reading-stats、/v3/admin/agent-stats）按时间范围查询用的索引。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_operation_logs', function (Blueprint $table) {
            $table->index(['op_type', 'created_at']);
        });
        Schema::table('views', function (Blueprint $table) {
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_operation_logs', function (Blueprint $table) {
            $table->dropIndex(['op_type', 'created_at']);
        });
        Schema::table('views', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
        });
    }
};
