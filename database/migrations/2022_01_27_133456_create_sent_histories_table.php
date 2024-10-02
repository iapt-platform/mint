<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     /*
         id SERIAL PRIMARY KEY,
	sent_uid VARCHAR (36), 
	user_uid VARCHAR (36), 
	content TEXT, 
	landmark VARCHAR (64),
	date BIGINT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
     */
    public function up()
    {
        Schema::create('sent_histories', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();
			$table->string('sent_uid',36)->index();
			$table->string('user_uid',36);
			$table->text('content');
			$table->string('landmark',64)->nullable();

			$table->bigInteger('create_time')->index();
			$table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sent_histories');
    }
}
