<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 给 discussions 增加评估分数 score，只有 type=assessment（译文评估）有值。
     * 统一归一到 0–100 的整数：译文质量是模糊评估，小数没有意义。评估方法记在 severity 里。
     *
     * nullable：存量数据与 dashboard-v4（v2 写入）都不会带这个值。
     */
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->smallInteger('score')->nullable()->index()
                ->comment('评估分数 0–100，只有 type=assessment 有值');
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }
};
