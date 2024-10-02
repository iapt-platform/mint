<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

			$table->string('uid',36)->uniqid()->index();
			$table->bigInteger('parent_id')->nullable()->index();
			$table->bigInteger('default_channel_id')->nullable()->index();
			$table->string('title',128)->index();
			$table->string('subtitle',128)->nullable();
			$table->string('summary',1024)->nullable();
			$table->text('cover')->nullable();
            $table->text('article_list')->nullable();
            $table->string('owner',36)->index();
            $table->bigInteger('owner_id')->index();
            $table->bigInteger('editor_id')->index();
			$table->text('setting')->nullable();
            $table->integer('status')->default(10)->index();
            $table->string('lang',16)->default('none')->index();

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
        Schema::dropIfExists('collections');
    }
}
