<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileIndicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_indices', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

            $table->string('uid',36)->uniqid();
            $table->string('parent_id',36)->nullable()->index();
            $table->bigInteger('user_id')->index();
            $table->integer('book');
            $table->integer('paragraph');
            $table->string('channal',36)->nullable()->index();
            $table->string('file_name',128)->nullable()->index();
            $table->string('title',128)->index();
            $table->string('tag',512)->nullable()->index();
            $table->integer('status')->default(1);
            $table->integer('file_size')->nullable();
            $table->integer('share')->default(0);
            $table->text('doc_info');
            $table->text('doc_block');

            $table->bigInteger('create_time');
            $table->bigInteger('modify_time');
            $table->bigInteger('accese_time');

            $table->timestamp('created_at')->useCurrent()->index();
            $table->timestamp('accesed_at')->useCurrent()->index();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();
            $table->timestamp('deleted_at')->nullable();

            $table->index(['book','paragraph']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_indices');
    }
}
