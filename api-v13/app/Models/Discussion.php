<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $casts = [
        'id' => 'string',
    ];

    // 批量填充
    protected $fillable = [
        'res_id',
        'res_type',
        'type',
        'tpl_id',
        'title',
        'content',
        'content_type',
        'parent',
        'editor_uid',
    ];

    // 设置默认值
    protected $attributes = [
        'content_type' => 'markdown',
        'type' => 'discussion',
    ];
}
