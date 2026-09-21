<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 注释书对应从 type='note' 改叫 type='commentary'，把 note 让给「普通边注」。
     *
     * 两者的区别只在出处：commentary 的 content 是下一层的句子模板
     * `{{book-para-start-end}}`，渲染时带 <cite> 跳转；note 的 content 是注解正文，没有出处。
     * 所以只把 content 整体是句子模板的旧记录改名，其余 note 原样留着当普通边注。
     *
     * 走 query builder 而非模型：不触发 Discussion 的缓存清除事件——改名前后
     * 这些记录的渲染结果一样，阅读页缓存仍然有效。
     */
    private const SENTENCE_TPL = '^(\{\{[0-9]+-[0-9]+-[0-9]+-[0-9]+\}\}[[:space:]]*)+$';

    public function up(): void
    {
        DB::table('discussions')
            ->where('type', 'note')
            ->where('content', '~', self::SENTENCE_TPL)
            ->update(['type' => 'commentary']);
    }

    public function down(): void
    {
        DB::table('discussions')
            ->where('type', 'commentary')
            ->update(['type' => 'note']);
    }
};
