<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from')->index();
            $table->uuid('to')->index();
            $table->string('url',1024)->nullable();
            $table->text('content')->nullable()->index();
            $table->string('content_type',16)->index()->default('markdown');
            $table->string('res_type',32)->index();
            $table->uuid('res_id')->index();
            $table->string('status',32)->index()->default('unread');
            $table->softDeletes();
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
        Schema::dropIfExists('notifications');
    }
}
