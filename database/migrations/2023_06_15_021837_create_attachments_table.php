<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("user_uid")->index();
            $table->string('bucket',255)->index();
            $table->string('name',63)->index();
            $table->string('title',255)->index();
            $table->bigInteger('size')->index();
            $table->string('content_type',63);
            $table->string('status',16)->index();
            $table->integer('version')->default(0);
            $table->timestamp('deleted_at')->nullable()->index();
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
        Schema::dropIfExists('attachments');
    }
}
