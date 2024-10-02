<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelatedParagraphsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('related_paragraphs', function (Blueprint $table) {
            $table->id();
            $table->integer('book')->index();
            $table->integer('para')->index();
            $table->integer('book_id');
            $table->integer('cs_para');
            $table->string('book_name',64)->index();
            $table->timestamps();
            $table->index(['book','para','cs_para']);
            $table->index(['book_name','cs_para']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('related_paragraphs');
    }
}
