<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('type',1)->index();
            $table->integer('volume')->index();
            $table->integer('page')->index();
            $table->integer('book')->index();
            $table->integer('paragraph')->index();
            $table->integer('wid')->index();
            $table->integer('pcd_book_id')->index();
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
        Schema::dropIfExists('page_numbers');
    }
}
