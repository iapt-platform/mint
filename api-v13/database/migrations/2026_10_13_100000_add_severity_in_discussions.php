<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 给 discussions 增加严重/紧急程度 severity，取值随 type 而定：
     * - type=issue → error / warning / suggestion
     * - type=help  → urgent / normal
     *
     * nullable：存量数据与 dashboard-v4（v2 写入）都不会带这个值。
     */
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->string('severity', 16)->nullable()->index()
                ->comment('严重/紧急程度，取值随 type 而定');
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
};
