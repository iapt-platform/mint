<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbwBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		/*

 

 
    style VARCHAR (16), 
    lang VARCHAR (16)  NOT NULL, 
    status INTEGER  NOT NULL, 
		*/
        Schema::create('wbw_blocks', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
			$table->string('uid',36)->uniqid()->index();			
			$table->string('parent_id',36)->nullable();
			$table->string('block_uid',36)->nullable()->index();
            $table->bigInteger('block_id')->nullable()->index();
            $table->bigInteger('channel_id')->nullable()->index();
            $table->string('channel_uid',36)->nullable()->index();
			$table->string('parent_channel_uid',36)->nullable();
			$table->string('creator_uid',36);
            $table->bigInteger('editor_id');
			$table->integer('book_id');
			$table->integer('paragraph');
			$table->string('style',16)->nullable();
			$table->string("lang",16);
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
        Schema::dropIfExists('wbw_blocks');
    }
}
