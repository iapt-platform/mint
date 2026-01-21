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
        Schema::create('commentaries', function (Blueprint $table) {
            $table->id();
            $table->integer('book1')->index()->comment('注释书号');
            $table->integer('paragraph1')->index()->comment('注释书段落');
            $table->integer('start1')->index()->comment('注释书句子单词起始');
            $table->integer('end1')->index()->comment('注释书句子单词结束');
            $table->integer('book2')->nullable()->index();
            $table->integer('paragraph2')->nullable()->index();
            $table->integer('start2')->nullable()->index();
            $table->integer('end2')->nullable()->index();
            $table->string('p_number')->index()->comment('段落号');
            $table->uuid('owner_id')->index();
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
        Schema::dropIfExists('commentaries');
    }
};
