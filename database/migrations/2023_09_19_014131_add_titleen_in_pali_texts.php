<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleenInPaliTexts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
    public function down()
    {
        Schema::table('pali_texts', function (Blueprint $table) {
            //
            $table->dropColumn('title_en');
            $table->dropColumn('title');
            $table->dropColumn('pcd_book_id');
        });
    }
}
