<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dict_infos', function (Blueprint $table) {
			$table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
			$table->string('name',256)->index();
			$table->string('shortname',16)->index();
			$table->string('description',1024)->nullable();
			$table->string('src_lang',16)->index()->default('pa');
			$table->string('dest_lang',16)->index()->default('en');
			$table->integer('rows')->default(0);
			$table->uuid('owner_id');
			$table->json('meta');
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
        Schema::dropIfExists('dict_infos');
    }
}
