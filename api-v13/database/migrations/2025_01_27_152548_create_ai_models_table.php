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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('name', 64)->index();
            $table->string('real_name', 64)->unique();
            $table->uuid('avatar',)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('url', 1024)->nullable()->index();
            $table->string('model', 1024)->nullable()->index();
            $table->string('key', 1024)->nullable();
            $table->text('system_prompt')->nullable();
            $table->string('privacy', 32)->index()->default('private')->comment('隐私性:private|public');
            $table->uuid('owner_id')->index()->comment('任务拥有者:用户或者team-space');
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
        Schema::dropIfExists('ai_models');
    }
};
