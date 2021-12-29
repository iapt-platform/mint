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
            $table->integer('lenght')->nullable();
            $table->integer('album_index')->nullable();
            $table->integer('chapter_len')->nullable();
            $table->integer('next_chapter')->nullable();
            $table->integer('prev_chapter')->nullable();
            $table->integer('parent')->nullable();
            $table->integer('chapter_strlen')->nullable();

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
