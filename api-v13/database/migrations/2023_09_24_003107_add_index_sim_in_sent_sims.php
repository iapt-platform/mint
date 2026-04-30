<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('sent_sims', function (Blueprint $table) {
            //
            $table->index('sim');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sent_sims', function (Blueprint $table) {
            //
            $table->dropIndex("sent_sims_sim_index");
        });
    }
};
