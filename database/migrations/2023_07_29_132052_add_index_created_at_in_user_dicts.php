<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexCreatedAtInUserDicts extends Migration
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
            $table->index('created_at');
            $table->index('updated_at');
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
            $table->dropIndex("user_dicts_created_at_index");
            $table->dropIndex("user_dicts_updated_at_index");

        });
    }
}
