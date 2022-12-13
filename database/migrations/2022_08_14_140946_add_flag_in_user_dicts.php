<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagInUserDicts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_dicts', function (Blueprint $table) {
            //
			$table->integer('flag')->default(0)->index();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_dicts', function (Blueprint $table) {
            //
            $table->dropColumn('flag');

        });
    }
}
