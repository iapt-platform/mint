<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_titles', function (Blueprint $table) {
            $table->id();
			$table->integer('book');
            $table->integer('paragraph')->index();
            $table->string('title',1024);
            $table->timestamps();
            $table->unique(["book", "paragraph"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_titles');
    }
}
