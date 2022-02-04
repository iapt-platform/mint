<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDhammaTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dhamma_terms', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();
            $table->string('guid',36)->uniqid();

            $table->string('word',1024)->index();
            $table->string('word_en',1024)->index();
            $table->string('meaning',1024)->index();
            $table->string('other_meaning',1024)->nullable();
            $table->text('note',1024)->nullable();
            $table->string('tag',1024)->nullable();
            $table->string('channal',36)->index()->nullable();
            $table->string('language',16)->default('zh-hans');
            $table->string('owner',36)->index();
            $table->bigInteger('editor_id')->index();

            $table->bigInteger('create_time');
            $table->bigInteger('modify_time');

			$table->timestamp('created_at')->useCurrent()->index();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();
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
        Schema::dropIfExists('dhamma_terms');
    }
}
