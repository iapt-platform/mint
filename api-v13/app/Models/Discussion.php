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

    /**
     * 注释记录（type='note'）会被注入到所挂句子的阅读页里（PaliContentService::
     * injectAnnotationNotes），阅读页按 (book, para, channel) 缓存，所以增改删 note
     * 都要清掉那一段的缓存。改之前是 note、改之后不是（或反过来）的也要清。
     */
    protected static function booted(): void
    {
        $forget = function (Discussion $discussion) {
            $wasNote = $discussion->getOriginal('type') === 'note';
            if ($discussion->type !== 'note' && ! $wasNote) {
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
