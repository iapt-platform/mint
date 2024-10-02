<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWbwAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     
     * @return void
     */
     /*
     'userid',
                            'pali',
                            'book',
                            'paragraph',
                            'wid',
                            'type',
                            'data',
                            'confidence',
                            'lang',
                            'modify_time')
     */
    public function up()
    {
        Schema::create('wbw_analyses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('wbw_id')->index();
            $table->string('wbw_word',1024);
            $table->integer('book_id');
            $table->integer('paragraph');
            $table->integer('wid');
            $table->integer('type');
            $table->text('data')->index();
            $table->integer('confidence');
            $table->string('lang',16);
            $table->text('d1')->nullable()->index();//备用数据
            $table->text('d2')->nullable()->index();//备用数据
            $table->text('d3')->nullable()->index();//备用数据
            $table->bigInteger('editor_id');
            $table->timestamps();

            $table->index(['type','data']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wbw_analyses');
    }
}
