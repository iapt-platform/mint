<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 译文集（channel）。表 `channels`，界面上叫「版本风格」。
 *
 * `owner_uid` 是用户 uuid 或团队（`group_infos.uid`）uuid——团队空间里的 channel 属于团队。
 * `status`：30 全网公开；20 公开不列出（拿到链接谁都能看，但不出现在社区目录、
 * 章节版本列表等任何面向他人的列表里）；10 私有（0 / 1 / 5 是历史值，按私有对待）。
 *
 * 软删除（2026-11-16 起）：v2 与 v3 的删除都只标 `deleted_at`，查询自动排除。
 * 删除前要求 channel 下没有译文、术语、逐词（v2 / v3 同口径），所以被删的 channel
 * 不会再出现在 progress_chapters 等派生表里。
 */
class Channel extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PRIVATE = 10;

    public const STATUS_UNLISTED = 20;

    public const STATUS_PUBLIC = 30;

    protected $fillable = ['name', 'summary', 'type', 'lang', 'status', 'updated_at', 'created_at'];

    protected $primaryKey = 'uid';

    /** 主键是 uuid：不声明的话关系预加载会按整数走 whereIntegerInRaw，把 uuid 全转成 0 */
    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

    /**
     * 反向关联到 ProgressChapter
     */
    public function progressChapters()
    {
        return $this->hasMany(ProgressChapter::class, 'channel_id', 'uid');
    }

    public function owner()
    {
        return $this->belongsTo(UserInfo::class, 'owner_uid', 'userid');
    }

    /**
     * 拥有它的团队（owner 是团队时才有）。
     */
    public function ownerGroup(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'owner_uid', 'uid');
    }
}
