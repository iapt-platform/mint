<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFtsTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
        Schema::create('fts_texts', function (Blueprint $table) {
            $table->id();
            $table->integer('book');
            $table->integer('paragraph');
            $table->string('wid',64);
            $table->text('bold_single');
            $table->text('bold_double');
            $table->text('bold_multiple');
            $table->text('content');
			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fts_texts');
    }
}
