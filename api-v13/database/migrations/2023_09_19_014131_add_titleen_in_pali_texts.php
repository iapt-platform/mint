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
        Schema::table('pali_texts', function (Blueprint $table) {
            //
            $table->text('title_en')->nullable()->index();
            $table->text('title')->nullable()->index();
            $table->integer('pcd_book_id')->index()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('pali_texts', function (Blueprint $table) {
            //
            $table->dropColumn('title_en');
            $table->dropColumn('title');
            $table->dropColumn('pcd_book_id');
        });
    }
};
