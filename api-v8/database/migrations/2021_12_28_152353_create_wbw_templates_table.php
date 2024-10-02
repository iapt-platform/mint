<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbwTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wbw_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('book');
            $table->integer('paragraph');
            $table->integer('wid');
			$table->string('word',1024)->index();
			$table->string('real',1024);
			$table->string('type',64);
			$table->string('gramma',64);
			$table->string('part',1024);
			$table->string('style',64);
			$table->timestamps();

            $table->index(["book", "paragraph", "wid"]);
            $table->index(["book", "paragraph"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wbw_templates');
    }
}
