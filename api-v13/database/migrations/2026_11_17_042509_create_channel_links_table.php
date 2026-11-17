<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * channel 之间的关系设置：继承自、降级、捆绑的逐词、术语路径。服务端只记录，怎么用由客户端决定。
     */
    public function up(): void
    {
        Schema::create('channel_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('channel_uid')->comment('设置方 channel（channels.uid）');
            $table->string('kind', 16)->comment('关系：derived_from 继承自（单值，只记录）/ fallback 降级（有序，段落缺数据时客户端改取它）/ wbw 捆绑的逐词 channel（单值，载入时当原文）/ term_source 术语路径（有序，从哪些 channel 取术语）');
            $table->uuid('target_uid')->comment('被指向的 channel（channels.uid）');
            $table->unsignedSmallInteger('position')->default(0)->comment('同一 kind 内的顺序，从 0 起；单值的 kind 恒为 0');
            $table->timestamps();

            $table->unique(['channel_uid', 'kind', 'target_uid']);
            $table->index(['target_uid', 'kind'], 'channel_links_target_kind_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_links');
    }
};
