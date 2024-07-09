<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEditorInCourseMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_members', function (Blueprint $table) {
            //
            $table->uuid('editor_uid')->index()->default(DB::raw('uuid_generate_v1mc()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_members', function (Blueprint $table) {
            //
            $table->dropColumn('editor_uid');
        });
    }
}
