<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_infos', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

            $table->string('uid',36)->uniqid()->index();
            $table->string('name',64)->uniqid()->index();
            $table->string('description',1024)->nullable();
            $table->integer('status')->default(30);
            $table->string('owner',36)->index();
            $table->bigInteger('create_time');
            $table->bigInteger('modify_time');
            $table->timestamp('created_at')->useCurrent()->index();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_infos');
    }
}
