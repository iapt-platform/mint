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
        Schema::table('channels', function (Blueprint $table) {
            //
            $table->renameColumn('type', 'type_old');
            $table->renameColumn('type2', 'type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            //
            $table->renameColumn('type', 'type2');
            $table->renameColumn('type_old', 'type');
        });
    }
};
