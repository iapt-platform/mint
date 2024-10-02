<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaliSentOrgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pali_sent_orgs', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('book');
            $table->integer('paragraph');
            $table->integer('word_begin');
            $table->integer('word_end');
            $table->integer('length');
            $table->integer('count');
            $table->text('text');
            $table->text('html');
            $table->integer('merge')->default(1);
            $table->text('sim_sents')->nullable();
            $table->text('sim_sents_count')->default(0);
			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

			$table->index(['book','paragraph','word_begin','word_end']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pali_sent_orgs');
    }
}
