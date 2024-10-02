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
        Schema::create('sentences', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

			$table->string('uid',36)->uniqid()->index();
			$table->string('parent_uid',36)->nullable()->index();
			$table->string('block_uid',36)->nullable()->index();
			$table->string('channel_uid',36)->nullable()->index();
			$table->integer('book_id');
			$table->integer('paragraph');
			$table->integer('word_start');
			$table->integer('word_end');
			$table->string('author',512)->nullable();

			$table->string('editor_uid',36);
            $table->text('content')->nullable();
            $table->enum('content_type',['markdown','text','html'])->default('markdown');
			$table->string('language',16);
			$table->integer('version')->default(1);
			$table->integer('strlen');
			$table->integer('status')->default(10);

			$table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();

			$table->index(['book_id','paragraph','word_start','word_end']);
			$table->index(['book_id','paragraph','word_start','word_end','channel_uid']);

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
