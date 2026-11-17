<?php

namespace App\Models;

use App\Services\V3\ChannelLinkService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * channel 之间的一条关系（`channel_links` 表）：channel_uid 指向 target_uid，kind 说明是哪种关系。
 *
 * 只给 v3 用，读写经 {@see ChannelLinkService}。
 */
class ChannelLink extends Model
{
    /** 继承自（如英文版译自中文版）。单值，只记录 */
    public const DERIVED_FROM = 'derived_from';

    /** 降级：段落没有数据时改取的 channel。有序，由客户端拼接 */
    public const FALLBACK = 'fallback';

    /** 捆绑的逐词 channel，载入时当原文。单值 */
    public const WBW = 'wbw';

    /** 术语路径：从哪些 channel 取术语。有序 */
    public const TERM_SOURCE = 'term_source';

    /** 单值的 kind */
    public const SINGLE = [self::DERIVED_FROM, self::WBW];

    /** 有序列表的 kind → 条数上限 */
    public const LISTS = [self::FALLBACK => 3, self::TERM_SOURCE => 10];

    protected $fillable = ['channel_uid', 'kind', 'target_uid', 'position'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'target_uid', 'uid');
    }
}
