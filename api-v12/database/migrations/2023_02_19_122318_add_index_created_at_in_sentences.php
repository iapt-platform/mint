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
        Schema::table('sentences', function (Blueprint $table) {
            //
            $table->index('created_at');
            $table->index('updated_at');
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
            //
            $table->dropIndex("sentences_created_at_index");
            $table->dropIndex("sentences_updated_at_index");
        });
    }
};
