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
        Schema::table('sent_histories', function (Blueprint $table) {
            //
            $table->uuid('fork_from')->nullable()->index();
            $table->uuid('pr_from')->nullable()->index();
            $table->uuid('accepter_uid')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sent_histories', function (Blueprint $table) {
            //
            $table->dropColumn('fork_from');
            $table->dropColumn('pr_from');
            $table->dropColumn('accepter_uid');
        });
    }
};
