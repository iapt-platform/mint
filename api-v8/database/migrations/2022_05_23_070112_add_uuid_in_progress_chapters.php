<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidInProgressChapters extends Migration
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
            $table->uuid('uid')->unique()->default(DB::raw('uuid_generate_v1mc()'));
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
            $table->dropColumn('uid');
        });
    }
}
