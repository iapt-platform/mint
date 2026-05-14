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
        Schema::table('dhamma_terms', function (Blueprint $table) {
            $table->string('redirect', 1024)
                ->nullable()->comment('重定向到另一个术语');

            // 添加普通索引
            $table->index('redirect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dhamma_terms', function (Blueprint $table) {
            //
            $table->dropIndex(['redirect']);
            $table->dropColumn('redirect');
        });
    }
};
