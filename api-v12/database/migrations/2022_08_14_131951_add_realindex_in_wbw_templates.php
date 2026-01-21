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
        Schema::table('wbw_templates', function (Blueprint $table) {
            //
            $table->index(['real']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('wbw_templates', function (Blueprint $table) {
            //
            $table->dropIndex('wbw_templates_real_index');
        });
    }
};
