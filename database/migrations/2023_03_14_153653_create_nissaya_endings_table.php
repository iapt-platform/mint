<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNissayaEndingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nissaya_endings', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->string('ending',256)->index();
            $table->string('lang',16)->index();
            $table->string('relation',32)->nullable()->index();
            $table->string('case',32)->nullable()->index();
            $table->integer('strlen')->index()->default(0);
            $table->integer('count')->index()->default(0);
            $table->uuid('editor_id');
            $table->timestamps();
            $table->unique(["ending", "relation","case"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nissaya_endings');
    }
}
