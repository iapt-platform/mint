<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // ① 先清洗数据（保证可转换）
        DB::statement("
            UPDATE sentences
            SET channel_uid = NULL
            WHERE channel_uid IS NOT NULL
              AND trim(channel_uid) = ''
        ");

        // ② 再修改字段类型
        DB::statement("
            ALTER TABLE sentences
            ALTER COLUMN channel_uid TYPE uuid
            USING channel_uid::uuid
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE sentences ALTER COLUMN channel_uid TYPE varchar(36) USING channel_uid::text');
    }
};
