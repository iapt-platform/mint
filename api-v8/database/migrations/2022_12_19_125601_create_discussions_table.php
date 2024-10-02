<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscussionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discussions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
			$table->uuid('res_id')->index();
			$table->string('res_type',32)->index();
			$table->uuid('parent')->index()->nullable();
			$table->string('title',256)->nullable();
			$table->text('content')->nullable();
            $table->integer('children_count')->default(0);
			$table->uuid('editor_uid')->index();
			$table->string('publicity',32)->default('public');
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
        Schema::dropIfExists('discussions');
    }
}
