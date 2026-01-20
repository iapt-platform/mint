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
        Schema::create('attachment_maps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('res_type', 64)->index();
            $table->uuid('res_id')->index();
            $table->uuid('attachment_id')->index();
            $table->uuid('editor_id')->index();
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
        Schema::dropIfExists('attachment_maps');
    }
};
