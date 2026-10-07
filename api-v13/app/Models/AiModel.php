<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    use HasFactory;

    protected $primaryKey = 'uid';

    /**
     * 主键 `uid` 是 uuid 字符串。
     *
     * 不声明这一行，Eloquent 按默认的 int 主键处理，关系预加载会走
     * `whereIntegerInRaw`，把 uuid 全部强转成 0（`where "uid" in (0)`）。
     * 下面的 casts 本来就写着 string，这里只是把两处声明对齐。
     *
     * 只影响关系预加载的取值方式；`$incrementing` 未动，写入行为不变。
     */
    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];
}
