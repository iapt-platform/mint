<?php

namespace App\Models;

use App\Services\PaliContentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $casts = [
        'id' => 'string',
        'pos_start' => 'integer',
        'pos_end' => 'integer',
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
        'pos_start',
        'pos_end',
        'quote_exact',
        'quote_prefix',
        'quote_suffix',
    ];

    /** 注释书对应：content 是下一层句子模板，渲染时带 <cite> 出处 */
    public const TYPE_COMMENTARY = 'commentary';

    /** 普通边注：content 就是注解正文，没有出处 */
    public const TYPE_NOTE = 'note';

    /** 会被注入阅读页的批注类型 */
    public const INJECTED_TYPES = [self::TYPE_COMMENTARY, self::TYPE_NOTE];

    /**
     * 这两类批注会被注入到所挂句子的阅读页里（PaliContentService::
     * injectAnnotationNotes），阅读页按 (book, para, channel) 缓存，所以增改删它们
     * 都要清掉那一段的缓存。改之前是、改之后不是（或反过来）的也要清。
     */
    protected static function booted(): void
    {
        $forget = function (Discussion $discussion) {
            $wasInjected = in_array($discussion->getOriginal('type'), self::INJECTED_TYPES, true);
            if (! in_array($discussion->type, self::INJECTED_TYPES, true) && ! $wasInjected) {
                return;
            }
            if ($discussion->res_type !== 'sentence' || empty($discussion->res_id)) {
                return;
            }
            $sentence = Sentence::where('uid', $discussion->res_id)
                ->first(['book_id', 'paragraph', 'channel_uid']);
            if (! $sentence) {
                return;
            }
            PaliContentService::forgetParagraph(
                (int) $sentence->book_id,
                (int) $sentence->paragraph,
                (string) $sentence->channel_uid
            );
        };
        static::saved($forget);
        static::deleted($forget);
    }

    // 设置默认值
    protected $attributes = [
        'content_type' => 'markdown',
        'type' => 'discussion',
    ];
}
