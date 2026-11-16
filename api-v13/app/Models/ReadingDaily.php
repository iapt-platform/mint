<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 登录用户某一天（UTC）的读经时长。
 */
class ReadingDaily extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'day' => 'date:Y-m-d',
        'last_report_at' => 'datetime',
    ];
}
