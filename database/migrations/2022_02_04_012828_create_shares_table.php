<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shares', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();

            $table->string('res_id',36);
            $table->integer('res_type');
            $table->string('cooperator_id',36);
            $table->integer('cooperator_type');
            $table->integer('power');
            $table->bigInteger('create_time');
            $table->bigInteger('modify_time');

			$table->timestamp('accepted_at')->nullable()->index();
            $table->bigInteger('acceptor')->nullable();

			$table->timestamp('created_at')->useCurrent()->index();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();

            $table->unique(['res_id','res_type','cooperator_id','cooperator_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shares');
    }
}
