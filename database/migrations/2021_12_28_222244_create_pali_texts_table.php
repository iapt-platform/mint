<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaliTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pali_texts', function (Blueprint $table) {
            $table->id();
			$table->integer('book');
            $table->integer('paragraph');
            $table->integer('level');
            $table->string('class',255);
            $table->text('toc');
            $table->text('text');
            $table->text('html');
            $table->integer('lenght');
            $table->integer('album_index');
            $table->integer('chapter_len');
            $table->integer('next_chapter');
            $table->integer('prev_chapter');
            $table->integer('parent');
            $table->integer('chapter_strlen');

            $table->timestamps();

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
        Schema::dropIfExists('pali_texts');
    }
}
