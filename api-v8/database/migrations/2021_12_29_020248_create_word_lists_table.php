<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('word_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('sn')->unique();
            $table->integer('book');
            $table->integer('paragraph');
            $table->integer('wordindex')->index();
            $table->integer('bold')->default(0);
            $table->integer('weight')->default(1);
			$table->timestamps();

            $table->index(["book", "paragraph"]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('word_lists');
    }
}
