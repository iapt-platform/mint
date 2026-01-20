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
            $table->dateTime('fork_at')->nullable()->index();
            $table->integer('collaborator')->index()->default(0);
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
            $table->dropColumn('fork_at');
            $table->dropColumn('collaborator');
        });
    }
};
