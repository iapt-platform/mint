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
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->uuid('user_id');
            $table->date('date');//统计日期
            $table->integer('user_exp')->default(0);//总分
            $table->integer('user_level')->default(1);//等级 1-60
            $table->integer('edit_exp')->default(0);//编辑总时长 小时
            $table->integer('wbw_count')->default(0);//逐词译个数
            $table->integer('wbw_edit')->default(0);//逐词译编辑次数
            $table->integer('trans_character')->default(0);//译文字符数
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
