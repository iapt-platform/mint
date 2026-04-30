<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('tag_maps', function (Blueprint $table) {
            //
            $table->uuid('owner_uid')->index()->default(config("mint.admin.root_uuid"));
            $table->uuid('editor_uid')->index()->default(config("mint.admin.root_uuid"));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('tag_maps', function (Blueprint $table) {
            //
            $table->dropColumn('owner_uid');
            $table->dropColumn('editor_uid');
        });
    }
};
