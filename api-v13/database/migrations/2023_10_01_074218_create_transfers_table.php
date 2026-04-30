<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->uuid('origin_owner')->index();
            $table->string('res_type', 32)->index();
            $table->uuid('res_id')->index();
            $table->uuid('transferor_id')->index();
            $table->uuid('new_owner')->index();
            $table->uuid('editor_id')->nullable()->index();
            $table->string('status', 32)->index()->default('transferred');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
