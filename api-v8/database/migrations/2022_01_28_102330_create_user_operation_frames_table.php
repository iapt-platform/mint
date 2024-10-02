<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOperationFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_operation_frames', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();
		    $table->bigInteger('user_id')->index();
		    $table->bigInteger('duration');
            $table->integer('hit')->default(1);
		    $table->bigInteger('timezone')->default(0);
		    $table->bigInteger('op_start');
		    $table->bigInteger('op_end');
		    
			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_operation_frames');
    }
}
