<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResIndicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('res_indices', function (Blueprint $table) {
            $table->id();
            $table->integer('book');
            $table->integer('paragraph');
            $table->string('title',1024)->index();
            $table->string('title_en',1024)->index();
            $table->integer('level');
            $table->integer('type');
            $table->string('language',16);
            $table->string('author',256)->nullable();
            $table->integer('editor')->default(0);
            $table->integer('share')->default(1);
            $table->integer('edition')->default(1);
            $table->integer('hit')->default(0);
            $table->integer('album')->nullable();
            $table->string('tag',1024)->nullable();
            $table->string('summary',1024)->nullable();
            $table->bigInteger('create_time');
            $table->bigInteger('update_time');
            
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
        Schema::dropIfExists('res_indices');
    }
}
