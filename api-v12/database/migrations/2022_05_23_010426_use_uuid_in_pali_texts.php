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
        Schema::table('pali_texts', function (Blueprint $table) {
            //
            $table->uuid('uid')->unique()->default(DB::raw('uuid_generate_v1mc()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('pali_texts', function (Blueprint $table) {
            //
            $table->dropColumn('uid');
        });
    }
};
