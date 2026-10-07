<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 段落注释（义注/复注内嵌）：给 discussions 增加锚定选择器字段。
     *
     * 参照 W3C Web Annotation Data Model（https://www.w3.org/TR/annotation-model/）：
     * - pos_start / pos_end      → TextPositionSelector（start / end，字符位偏移）
     * - quote_exact              → TextQuoteSelector.exact（被锚定文本摘录）
     * - quote_prefix / quote_suffix → TextQuoteSelector.prefix / suffix（上下文，建议 32~64 字符）
     */
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            // 精定位：TextPositionSelector
            $table->integer('pos_start')->nullable()->index()
                ->comment('锚点在句子文本内的起始字符位（0 起，含标点）');
            $table->integer('pos_end')->nullable()->index()
                ->comment('锚点在句子文本内的结束字符位（不含）');
            // 精定位：TextQuoteSelector
            $table->text('quote_exact')->nullable()
                ->comment('被锚定文本的原文摘录');
            $table->text('quote_prefix')->nullable()
                ->comment('前缀上下文（建议 32~64 字符）');
            $table->text('quote_suffix')->nullable()
                ->comment('后缀上下文');
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn(['pos_start', 'pos_end', 'quote_exact', 'quote_prefix', 'quote_suffix']);
        });
    }
};
