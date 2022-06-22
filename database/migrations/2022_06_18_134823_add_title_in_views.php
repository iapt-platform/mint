<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleInViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('views', function (Blueprint $table) {
            //
			$table->string('title',256)->nullable()->index();
			$table->string('org_title',256)->nullable()->index();
			$table->integer('count')->default(0);
			$table->json('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('views', function (Blueprint $table) {
            //
            $table->dropColumn('title');
            $table->dropColumn('org_title');
            $table->dropColumn('count');
            $table->dropColumn('meta');

        });
    }
}
