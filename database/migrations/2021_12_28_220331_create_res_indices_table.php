<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResIndicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('res_indices', function (Blueprint $table) {
            $table->id();
            $table->integer('book');
            $table->integer('paragraph');
            $table->string('title',1024)->index();
            $table->string('title_en',1024)->index();
            $table->integer('level');
            $table->integer('type');
            $table->string('language',16);
            $table->string('author',256);
            $table->integer('editor');
            $table->integer('share');
            $table->integer('edition')->default(1);
            $table->integer('hit')->default(0);
            $table->integer('album');
            $table->string('tag',1024);
            $table->string('summary',1024);
            
			$table->timestamps('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('res_indices');
    }
}
