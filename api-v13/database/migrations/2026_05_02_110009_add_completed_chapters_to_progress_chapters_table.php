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
            $table->integer('completed_chapters')
                ->default(0)->comment('已经完成的字章节数量');
            $table->index('completed_chapters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_chapters', function (Blueprint $table) {
            $table->dropIndex(['completed_chapters']);
            $table->dropColumn('completed_chapters');
        });
    }
};
