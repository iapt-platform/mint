<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordIndicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('word_indices', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('word',1024)->unique();
            $table->string('word_en',1024)->index();
            $table->integer('count')->default(0);
            $table->integer('normal')->default(0);
            $table->integer('bold')->default(0);
            $table->integer('is_base')->default(0);
            $table->integer('len')->default(0);
            $table->integer('final')->default(0);
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
        Schema::dropIfExists('word_indices');
    }
}
