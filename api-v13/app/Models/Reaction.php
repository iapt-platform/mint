<?php

namespace App\Models;

use App\Http\Resources\V3\ReactionResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户对某个锚点（target）的单向操作记录。
 *
 * 点赞、收藏、书签、关注、下载记录共用这张表：type 区分操作种类，
 * 唯一键 (type, target_id, user_id) 保证「一个用户对一个锚点的同种操作只有一条」。
 *
 * 底层仍是历史表 `likes`（表名不迁移，v2 的 Like 模型继续指向同一张表）。
 */
class Reaction extends Model
{
    use HasFactory;

    /**
     * `type` 列的合法取值。
     *
     * 列本身是 varchar(32) 没有约束，这里是 v3 入口处收紧的白名单——
     * v2 的 `LikeController` 不做校验，历史数据里出现过的只有这几种。
     *
     * @var list<string>
     */
    public const TYPES = ['like', 'dislike', 'favorite', 'watch', 'bookmark', 'download'];

    /**
     * `target_type` 列的合法取值：锚点是哪一类对象。
     *
     * task 任务、collection 文集、progress_chapter 书·章节、article 文章、terms 术语。
     *
     * @var list<string>
     */
    public const TARGET_TYPES = ['task', 'collection', 'progress_chapter', 'article', 'terms'];

    protected $table = 'likes';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = ['type', 'target_id', 'target_type', 'user_id', 'context'];

    /**
     * 操作者是人类用户时的那一行。
     *
     * `user_id` 存的是 uuid，对应 `user_infos.userid`（不是主键 `id`），所以要写
     * 三参数形式。同款写法见 {@see Channel::owner()}。
     *
     * 关系没有数据库外键约束——`likes` 建表时就没有，加不得（migration 只准加
     * 不准改）。匹配列 `user_infos.userid` 上有唯一索引，whereIn 走得动。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'userid');
    }

    /**
     * 操作者是 AI 助手时的那一行。
     *
     * 同一个 `user_id` 落在 `user_infos` 或 `ai_models` 两张表之一，没有类型判别列，
     * 所以不是 morphTo，只能两条关系并存、取到哪个算哪个。
     * 见 {@see ReactionResource::toArray()}。
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'user_id', 'uid');
    }
}
