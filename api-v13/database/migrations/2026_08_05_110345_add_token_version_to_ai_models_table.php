<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 模型身份 token 的版本号。owner 撤销时自增，已签出 token 里的 ver 对不上即失效。
     */
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->unsignedInteger('token_version')
                ->default(1)
                ->comment('身份 token 版本号，自增即撤销该模型全部已签出 token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn('token_version');
        });
    }
};
