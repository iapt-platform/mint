<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 登录用户每天的读经时长（客户端上报、服务端核减后累加），用来换积分。
 *
 * 与 user_operation_dailies（编辑时长，由 v2 中间件按请求累计）是两回事：
 * 读经发生在 App 阅读器里，走 v3，不经过那个中间件。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_dailies', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uid')->comment('用户 uuid');
            $table->date('day')->comment('UTC 日');
            $table->unsignedInteger('seconds_reported')->default(0)->comment('客户端报上来的总秒数');
            $table->unsignedInteger('seconds')->default(0)->comment('核减后计入的秒数');
            $table->timestamp('last_report_at')->nullable()->comment('最近一次上报时间');
            $table->timestamps();

            $table->unique(['user_uid', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_dailies');
    }
};
