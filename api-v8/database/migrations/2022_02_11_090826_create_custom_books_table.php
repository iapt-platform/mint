<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_books', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('book_id')->uniqid();
            $table->string('title',512)->index();
            $table->string('owner',36)->index();
            $table->bigInteger('editor_id')->index();
            $table->string('lang',16);
            $table->integer('status');

            $table->timestamp('created_at')->useCurrent()->index();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_books');
    }
}
