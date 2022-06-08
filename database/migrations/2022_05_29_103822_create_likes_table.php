<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     /*

    点赞 like
    关注 watch
    收藏 favorite
    书签 bookmark
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->string('type',32)->index();
            $table->uuid('target_id')->index();
            $table->string('target_type',32)->index();
            $table->string('context',128)->nullable();
            $table->uuid('user_id')->index();
            $table->timestamps();

            $table->unique(['type','target_id','user_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
}
