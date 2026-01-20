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
        Schema::create('web_hooks', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('res_type', 32)->index();
            $table->uuid('res_id')->index();
            $table->string('url', 512)->index();
            $table->string('receiver', 32)->index();
            $table->json('event')->nullable();
            $table->integer('fail')->default(0);
            $table->integer('success')->default(0);
            $table->string('status', 16)->index()->default('active');
            $table->uuid("editor_uid")->index();
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
        Schema::dropIfExists('web_hooks');
    }
};
