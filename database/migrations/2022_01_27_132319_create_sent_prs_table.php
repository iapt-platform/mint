<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentPrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('sent_prs', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();
            //用作数据迁移时的相同数据比对
			$table->integer('old_id')->nullable();

			$table->integer('book_id');
			$table->integer('paragraph');
            $table->integer('word_start');
			$table->integer('word_end');
			$table->string('channel_uid',36);

			$table->string('author',512)->nullable();
			$table->string('editor_uid',36);

			$table->text('content');
			$table->string('language',16);
			$table->integer('status')->default(10);
			$table->integer('strlen')->default(0);

			$table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();

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
        Schema::dropIfExists('sent_prs');
    }
}
