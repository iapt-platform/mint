<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 术语 / 百科词条（`dhamma_terms` 表）。主键是 guid（uuid），另有雪花 `id` 列。
 *
 * 注意列名拼写：channel 存在 `channal` 列。channal 为空的是 studio 级通用术语（owner 名下所有 channel 共用）。
 * `word` + `tag`（义项）在一个 channel 内唯一，引用方写成 `[[word@tag]]`；v3 建立后不准改，
 * 要改就新建一条、把旧的 `redirect` 到新的（类似维基百科的重定向）。
 *
 * v3 新增的列（v2 不读）：co_editor_uid、quality、summary、content_type、version，
 * 分类在 {@see TermCategory}（term_categories 表）。v3 读写见 `App\Services\V3\TermService`。
 */
class DhammaTerm extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** 词条质量 */
    public const QUALITIES = ['featured', 'standard', 'draft', 'pending'];

    /** 正文方言：旧方言（v2 写入的都是它）与 v3 的 markdown-e2 */
    public const CONTENT_TYPES = ['markdown', 'markdown-e2'];

    public const CONTENT_TYPE_V3 = 'markdown-e2';

    protected $primaryKey = 'guid';

    protected $keyType = 'string';

    protected $casts = [
        'guid' => 'string',
        'version' => 'integer',
        'modify_time' => 'integer',
        'create_time' => 'integer',
    ];

    public $incrementing = false;

    protected $fillable = ['id', 'guid', 'word', 'word_en', 'meaning', 'channal', 'language', 'owner', 'editor_id', 'editor_uid', 'create_time', 'modify_time'];

    /** 所属 channel（channal 列）；studio 级术语为空 */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channal', 'uid');
    }

    /**
     * 所属 channel 的 uid；studio 级术语给 null。
     *
     * studio 级术语的 channal 有两种写法：NULL，以及 2022 年以前 v2 写入的空串（库里一千多条），
     * 判断「是不是 studio 级」一律走这里，不要直接比 `channal !== null`。
     */
    public function channelUid(): ?string
    {
        return $this->channal === null || $this->channal === '' ? null : (string) $this->channal;
    }

    /**
     * 只要 studio 级术语（channal 为 NULL 或空串，见 {@see self::channelUid()}）。
     *
     * @param  Builder<DhammaTerm>  $query
     */
    public function scopeStudioLevel(Builder $query): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('channal')->orWhere('channal', ''));
    }

    /** 编辑者（人类用户）。editor_uid 对 user_infos.userid */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'editor_uid', 'userid');
    }

    /** 编辑者（AI 助手）。存量数据里 AI 写的行 editor_uid 直接是模型 */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'editor_uid', 'uid');
    }

    /** 协作者：人类授权代写的 AI 模型。此时 editor 是授权人 */
    public function coEditor(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'co_editor_uid', 'uid');
    }

    /** 百科分类，按正文里出现的顺序 */
    public function categories(): HasMany
    {
        return $this->hasMany(TermCategory::class, 'term_guid', 'guid')->orderBy('position');
    }

    /** 实际方言：v2 写入的行库里是默认值 markdown */
    public function effectiveContentType(): string
    {
        return $this->content_type ?: 'markdown';
    }

    /** 乐观锁用的 ETag：v3 修改 +version，v2 修改只动 modify_time，两者都要算进去 */
    public function etag(): string
    {
        return '"'.((int) $this->version).'.'.((int) $this->modify_time).'"';
    }
}
