<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserExperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_experiences', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->date('date');//统计日期
            $table->integer('user_exp');//总分
            $table->integer('user_level');//等级 1-60
            $table->integer('edit_exp');//编辑总时长 小时
            $table->integer('wbw_count');//逐词译个数
            $table->integer('wbw_edit_time');//逐词译编辑次数
            $table->integer('trans_chart');//译文字符数
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_experiences');
    }
}
