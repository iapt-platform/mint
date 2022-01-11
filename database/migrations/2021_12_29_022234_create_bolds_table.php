<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bolds', function (Blueprint $table) {
            $table->id();
			$table->integer('book');
            $table->integer('paragraph');
            $table->string('word',1024)->index();
            $table->string('word2',1024);
            $table->string('word_en',1024)->index();
            $table->text('pali');
            $table->text('base');
			$table->timestamp('created_at')->useCurrent();
			$table->index(['book','paragraph']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bolds');
    }
}
