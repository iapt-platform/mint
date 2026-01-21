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
        Schema::table('wbw_analyses', function (Blueprint $table) {
            //
            $table->index('wbw_word');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('wbw_analyses', function (Blueprint $table) {
            $table->dropIndex("wbw_analyses_wbw_word_index");
            $table->dropIndex("wbw_analyses_type_index");
        });
    }
};
