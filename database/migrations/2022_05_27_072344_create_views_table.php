<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewsTable extends Migration
{
    /**
     * Run the migrations.
     * 点击量统计
     * @return void
     */
    public function up()
    {
        Schema::create('views', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->uuid('target_id')->index();
            $table->string('target_type',32)->index();
            $table->uuid('user_id')->index()->nullable();
            $table->ipAddress('user_ip')->index();
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
        Schema::dropIfExists('views');
    }
}
