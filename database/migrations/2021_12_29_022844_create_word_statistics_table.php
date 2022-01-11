<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('word_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('bookid')->index();
            $table->string('word',1024)->index();
            $table->integer('count');
            $table->string('base',1024)->index();
            $table->string('end1',256)->index();
            $table->string('end2',256)->index();
            $table->integer('type');
            $table->integer('length');
			$table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('word_statistics');
    }
}
