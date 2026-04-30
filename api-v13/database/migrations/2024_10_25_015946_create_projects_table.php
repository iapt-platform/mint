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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('title', 512)->index();
            $table->string('type', 32)->index()->default('normal');
            $table->text('description')->nullable();
            $table->integer('weight')->index()->default(0)->comment('权重');
            $table->integer('progress')->index()->default(0)->comment('进度0-100');
            $table->jsonb('executors_id')->index()->nullable();
            $table->uuid('parent_id')->index()->nullable();
            $table->jsonb('path')->index()->nullable();
            $table->jsonb('milestones')->index()->nullable();
            $table->uuid('owner_id')->index();
            $table->uuid('editor_id')->index();
            $table->jsonb('status')->index()->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
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
        Schema::dropIfExists('projects');
    }
};
