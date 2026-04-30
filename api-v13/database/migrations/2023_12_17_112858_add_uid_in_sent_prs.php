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
        Schema::table('sent_prs', function (Blueprint $table) {
            //
            $table->uuid('uid')->default(DB::raw('uuid_generate_v1mc()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sent_prs', function (Blueprint $table) {
            //
            $table->dropColumn('uid');
        });
    }
};
