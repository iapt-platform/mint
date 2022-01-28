<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_collections', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

			$table->string('collect_id',36)->index();
			$table->string('article_id',36)->index();
			$table->integer('level');
            $table->string('title',128)->nullable()->index();
			$table->integer('children')->default(0);
            $table->bigInteger('editor_id')->default(0);

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_collections');
    }
}
