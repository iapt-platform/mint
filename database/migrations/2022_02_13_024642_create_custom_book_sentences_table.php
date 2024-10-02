<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomBookSentencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_book_sentences', function (Blueprint $table) {
            $table->bigInteger('id')->primary();

            $table->integer('book')->index();
            $table->integer('paragraph');
            $table->integer('word_start');
            $table->integer('word_end');
            $table->text('content');
            $table->enum('content_type',['markdown','text','html'])->default('markdown');
            $table->integer('length');
            $table->string('owner',36)->index();
            $table->string('lang',16);
            $table->integer('status')->default(10);

            $table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();

            $table->timestamp('created_at')->useCurrent()->index();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();

            $table->index(['book','paragraph','word_start','word_end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_book_sentences');
    }
}
