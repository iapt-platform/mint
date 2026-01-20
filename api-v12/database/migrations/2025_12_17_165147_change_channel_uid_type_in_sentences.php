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
        Schema::table('sentences', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE sentences
            ALTER COLUMN channel_uid TYPE uuid
            USING CASE
                WHEN channel_uid = '' THEN NULL
                ELSE channel_uid::uuid
            END
        ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sentences', function (Blueprint $table) {
            DB::statement('ALTER TABLE sentences ALTER COLUMN channel_uid TYPE varchar(255) USING channel_uid::varchar');
        });
    }
};
