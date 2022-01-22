<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		/*

	parent_uid VARCHAR (36), 
	block_uid VARCHAR (36), 
	channel_uid VARCHAR (36), 
	book_id INTEGER NOT NULL, 
	paragraph INTEGER NOT NULL, 
	word_start INTEGER NOT NULL, 
	word_end INTEGER NOT NULL, 
	author TEXT, 
	editor_uid VARCHAR (36), 
	content TEXT, 
	language VARCHAR (16), 
	version INTEGER NOT NULL DEFAULT (1), 
	status INTEGER NOT NULL DEFAULT (10), 
	strlen INTEGER, 

		*/
        Schema::create('sentences', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

			$table->string('uid',36)->uniqid()->index();
			$table->string('parent_uid',36)->uniqid()->index();
			$table->string('block_uid',36)->uniqid()->index();
			$table->string('channel_uid',36)->uniqid()->index();
			$table->integer('book_id');
			$table->integer('paragraph');
			$table->integer('word_start');
			$table->integer('word_end');
			$table->string('author',512);

			$table->string('owner_uid',36);
            $table->bigInteger('editor_id');
			$table->string('name',128)->index();
			$table->string('summary',1024)->nullable();
			$table->string('lang',16);
			$table->integer('status')->default(10);
			$table->text('setting')->nullable();

			$table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sentences');
    }
}
