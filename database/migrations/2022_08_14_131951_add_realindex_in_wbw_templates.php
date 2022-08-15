<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRealindexInWbwTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wbw_templates', function (Blueprint $table) {
            //
			$table->index(['real']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wbw_templates', function (Blueprint $table) {
            //
            $table->dropIndex('wbw_templates_real_index');

        });
    }
}
