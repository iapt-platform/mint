<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbwsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wbws', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
			$table->string('uid',36)->uniqid()->index();
			$table->string('block_uid',36)->nullable()->index();
            $table->bigInteger('block_id')->nullable()->index();
            $table->bigInteger('channel_id')->nullable()->index();
			$table->integer('book_id');
			$table->integer('paragraph');
			$table->bigInteger('wid');
			$table->string("word",1024);
			$table->text("data")->default('');
			$table->integer('status')->default(10);
			$table->string('creator_uid',36);
            $table->bigInteger('editor_id');
            $table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();
 
			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();

			$table->index(['book_id','paragraph','wid']);
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
        Schema::dropIfExists('wbws');
    }
}
