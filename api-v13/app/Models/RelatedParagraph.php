<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 段落关联关系（mūla ← aṭṭhakathā ← ṭīkā 的注释对应）。
 *
 * 底层表 `related_paragraphs`。匹配键是 (book_name, cs_para)：相同 book_name + cs_para
 * 的段落互为注释关系。book_id 只用于输出书名（对应 book_titles.sn），不参与匹配。
 */
class RelatedParagraph extends Model
{
    use HasFactory;

    protected $table = 'related_paragraphs';

    /**
     * 相关段落的「真书」：book_id 对应 book_titles.sn（一个 level=1 书 = 一个 sn）。
     *
     * 没有数据库外键约束（建表时就没有），且 sn 上只有普通索引、不是唯一键——
     * 历史数据里同一 sn 可能对应多行，批量查询时用 whereIn('sn') 取。
     */
    public function bookTitle(): BelongsTo
    {
        return $this->belongsTo(BookTitle::class, 'book_id', 'sn');
    }
}
