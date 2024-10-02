<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidInSentPrs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sent_prs', function (Blueprint $table) {
            //
            $table->uuid('uid')->default(DB::raw('uuid_generate_v1mc()'));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sent_prs', function (Blueprint $table) {
            //
            $table->dropColumn('uid');
        });
    }
}
