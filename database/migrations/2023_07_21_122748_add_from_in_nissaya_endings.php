<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromInNissayaEndings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nissaya_endings', function (Blueprint $table) {
            //
            $table->json('from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nissaya_endings', function (Blueprint $table) {
            //
            $table->dropColumn('from');
        });
    }
}
