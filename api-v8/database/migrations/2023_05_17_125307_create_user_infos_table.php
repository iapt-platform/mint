<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->uuid('userid')->unique();
            $table->uuid('path')->nullable();
            $table->string('username',64)->unique();
            $table->string('password',64);
            $table->string('nickname',64);
            $table->string('email',256)->unique();
            $table->bigInteger('create_time')->nullable();
            $table->bigInteger('modify_time')->nullable();
            $table->bigInteger('receive_time')->nullable();
            $table->text('setting')->nullable();
            $table->text('reset_password_token')->nullable();
            $table->timestamp('reset_password_sent_at')->nullable();
            $table->text('confirmation_token')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->text('unconfirmed_email')->nullable();
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
        Schema::dropIfExists('user_infos');
    }
}
