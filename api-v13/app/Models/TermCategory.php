<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 术语的一个百科分类（`term_categories` 表）。由正文里的 `{{category|…}}` 抽出，
 * v3 写入术语时整体重建，不单独增删。
 */
class TermCategory extends Model
{
    protected $fillable = ['term_guid', 'category', 'position'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(DhammaTerm::class, 'term_guid', 'guid');
    }
}
