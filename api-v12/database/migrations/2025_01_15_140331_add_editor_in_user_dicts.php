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
        Schema::table('user_dicts', function (Blueprint $table) {
            //
            $table->uuid('editor_id')->nullable()->index()->comment('此次编辑者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('user_dicts', function (Blueprint $table) {
            //
            $table->dropColumn('editor_id');
        });
    }
};
