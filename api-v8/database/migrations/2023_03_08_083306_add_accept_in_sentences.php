<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcceptInSentences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentences', function (Blueprint $table) {
            //
            $table->dateTimeTz('pr_edit_at')->nullable()->index();
            $table->uuid('acceptor_uid')->nullable()->index();
            $table->bigInteger('pr_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sentences', function (Blueprint $table) {
            //
            $table->dropColumn('pr_edit_at');
            $table->dropColumn('acceptor_uid');
            $table->dropColumn('pr_id');
        });
    }
}
