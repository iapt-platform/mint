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
            $table->timestamp('last_chapter_completed_at')
                ->nullable()
                ->comment('add new chapter');
            $table->index('last_chapter_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_chapters', function (Blueprint $table) {
            $table->dropIndex(['last_chapter_completed_at']);
            $table->dropColumn('last_chapter_completed_at');
        });
    }
};
