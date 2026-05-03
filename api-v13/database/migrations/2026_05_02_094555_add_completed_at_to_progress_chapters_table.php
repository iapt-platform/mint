<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('progress_chapters', function (Blueprint $table) {
            $table->timestamp('completed_at')
                ->nullable()
                ->comment('chapter complete time');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_chapters', function (Blueprint $table) {
            $table->dropIndex(['completed_at']);
            $table->dropColumn('completed_at');
        });
    }
};
