<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelToProgressChapter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('progress_chapters', function (Blueprint $table) {
            //
            $table->uuid('channel_id')->index();
            $table->float('progress');
            $table->string('title',256)->nullable()->index();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('progress_chapters', function (Blueprint $table) {
            //
            $table->dropColumn('channel_id');
            $table->dropColumn('progress');
            $table->dropColumn('title');
        });
    }
}
