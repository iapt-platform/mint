<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 团队（group）。表 `group_infos`，界面上叫「团队」，团队空间以它为 owner。
 *
 * `status`：30 公开（能被搜到、非成员也能看详情），10 私有（只有成员看得到）。
 */
class GroupInfo extends Model
{
    use HasFactory;

    protected $primaryKey = 'uid';

    /** 主键是 uuid：不声明的话关系预加载会按整数走 whereIntegerInRaw，把 uuid 全转成 0 */
    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public const STATUS_PRIVATE = 10;

    public const STATUS_PUBLIC = 30;

    /**
     * 创建者（拥有者）。`owner` 是用户 uuid，对 `user_infos.userid`。
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'owner', 'userid');
    }

    /**
     * 共享给这个团队的资源（「团队文档」）。`shares.cooperator_id` 对团队 uid。
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'cooperator_id', 'uid');
    }

    /**
     * 团队成员。嵌套路由 `groups/{group}/members/{member}` 靠它做 scoped 绑定。
     */
    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id', 'uid');
    }
}
