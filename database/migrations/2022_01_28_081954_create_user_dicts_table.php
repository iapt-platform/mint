<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     /*
    id SERIAL PRIMARY KEY,
	word VARCHAR(1024)  NOT NULL , 
	type VARCHAR(128), 
	gramma VARCHAR(128), 
	stem VARCHAR(1024), 
	mean TEXT, 
	note TEXT, 
	factors VARCHAR(1024), 
	factormean TEXT, 
	status INTEGER  NOT NULL DEFAULT (1),
	source VARCHAR(256), 
	language VARCHAR(16)  NOT NULL DEFAULT ('en'), 
	confidence INTEGER  NOT NULL DEFAULT (100), 
	creator_id INTEGER   NOT NULL , 
	ref_counter INTEGER DEFAULT (1),
	create_time INTEGER  NOT NULL ,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
     */
    public function up()
    {
        Schema::create('user_dicts', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

			$table->string('word',1024)->index();
			$table->string('type',128)->nullable();
			$table->string('gramma',128)->nullable();
			$table->string('parent',1024)->nullable();
			$table->text('mean')->nullable();
			$table->text('note')->nullable();
			$table->string('factors',1024)->nullable();
			$table->string('factormean',1024)->nullable();
		    $table->integer('status')->default(10);
			$table->string('source',1024)->nullable();

			$table->string('language',16)->default('en-US');
		    $table->integer('confidence')->default(100);
		    $table->integer('exp')->default(0);
            $table->bigInteger('creator_id')->index();
            $table->bigInteger('ref_counter')->default(1);

			$table->bigInteger('create_time')->index();

			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			$table->timestamp('deleted_at')->nullable();

			$table->index(['word','creator_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_dicts');
    }
}
