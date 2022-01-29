<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     /*
     CREATE TABLE sent_blocks 
(
    id SERIAL PRIMARY KEY,
	uid VARCHAR (36), 
	parent_uid VARCHAR (36), 
	book_id INTEGER, 
	paragraph INTEGER, 
	owner_uid VARCHAR (36), 
	lang VARCHAR (16), 
	author VARCHAR (50), 
	editor_uid VARCHAR (36),
	status INTEGER NOT NULL DEFAULT (10), 
	modify_time BIGINT, 
	create_time BIGINT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX sent_blocks_uid ON sent_blocks (uid);
CREATE INDEX sent_blocks_book_para ON sent_blocks (book_id,paragraph);
     */
    public function up()
    {
        Schema::create('sent_blocks', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();
			$table->string('uid',36)->uniqid()->index();
			$table->string('parent_uid',36)->nullable()->index();
			$table->integer('book_id');
			$table->integer('paragraph');
			$table->string('owner_uid',36);
			$table->string('editor_uid',36);
			$table->string('lang',16);
			$table->string('author',64);
			$table->integer('status')->default(10);

			$table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();

			$table->index(['book_id','paragraph']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sent_blocks');
    }
}
