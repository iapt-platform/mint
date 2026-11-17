<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 术语的百科分类。分类写在正文的 {{category|…}} 里，v3 写入时抽出来存这里，百科页按它查（不再经 OpenSearch）。
     */
    public function up(): void
    {
        Schema::create('term_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('term_guid')->comment('术语（dhamma_terms.guid）');
            $table->string('category', 128)->comment('分类名，取自正文 {{category|…}}，原样保存、不限于 config/taxonomy.php');
            $table->unsignedSmallInteger('position')->default(0)->comment('在正文里出现的顺序，从 0 起');
            $table->timestamps();

            $table->unique(['term_guid', 'category']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_categories');
    }
};
