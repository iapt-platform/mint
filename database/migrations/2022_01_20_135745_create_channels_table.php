<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		/*
			uid VARCHAR (36) NOT NULL, 
			type TChannelType  NOT NULL DEFAULT('translation'),
			owner_uid  VARCHAR (36) NOT NULL, 
			editor_id BIGINT NOT NULL,
			name VARCHAR (64), 
			summary VARCHAR (1024),
			status INTEGER  NOT NULL DEFAULT(10), 
			lang VARCHAR (16), 
			setting TEXT,
			create_time BIGINT NOT NULL, 
			modify_time BIGINT NOT NULL, 
		*/
        Schema::create('channels', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
			$table->string('uid',36)->uniqid()->index();
			$table->enum('type',['original','translation','nissaya','commentary'])->default('translation');
			$table->string('owner_uid',36);
            $table->bigInteger('editor_id');
			$table->string('name',128)->index();
			$table->string('summary',1024)->nullable();
			$table->string('lang',16);
			$table->integer('status')->default(10);
			$table->text('setting')->nullable();

			$table->bigInteger('create_time')->index();
            $table->bigInteger('modify_time')->index();

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
}
