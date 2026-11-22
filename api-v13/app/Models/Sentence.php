<?php

namespace App\Models;

use App\Services\PaliContentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sentence extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'uid',
        'book_id',
        'paragraph',
        'word_start',
        'word_end',
        'channel_uid',
        'editor_uid',
        'content',
        'content_type',
        'strlen',
        'status',
        'create_time',
        'modify_time',
        'language',
    ];

    protected $primaryKey = 'uid';

    protected $casts = [
        'uid' => 'string',
        'channel_uid' => 'string',
    ];

    /**
     * 增改删句子时清阅读缓存：段落成品（L1）删掉；「有译文的段」可能变了才删内容索引（L2）。
     * 见 {@see PaliContentService::paragraphChanged()}。改了 book / paragraph / channel 的，新旧两处都要清。
     * 提交之后才清，免得并发的读在提交前把旧数据写回缓存。
     */
    protected static function booted(): void
    {
        $created = function (Sentence $sentence) {
            $now = [(int) $sentence->book_id, (int) $sentence->paragraph, (string) $sentence->channel_uid];
            $hasText = (int) $sentence->strlen > 0;
            DB::afterCommit(fn () => PaliContentService::paragraphChanged(...$now, indexChanged: true, mayAddBook: $hasText));
        };
        // updated 事件里 getOriginal() 还是改之前的值（含软删恢复：deleted_at 从有到无）
        $updated = function (Sentence $sentence) {
            $now = [(int) $sentence->book_id, (int) $sentence->paragraph, (string) $sentence->channel_uid];
            $before = [(int) $sentence->getOriginal('book_id'), (int) $sentence->getOriginal('paragraph'), (string) $sentence->getOriginal('channel_uid')];
            $hasText = (int) $sentence->strlen > 0;
            $hadText = (int) $sentence->getOriginal('strlen') > 0;
            $restored = $sentence->getOriginal('deleted_at') !== null && $sentence->deleted_at === null;
            DB::afterCommit(function () use ($now, $before, $hasText, $hadText, $restored) {
                $moved = $before !== $now;
                PaliContentService::paragraphChanged(
                    ...$now,
                    indexChanged: $restored || $moved || $hasText !== $hadText,
                    mayAddBook: $hasText,
                    mayRemoveBook: ! $moved && $hadText && ! $hasText,
                );
                if ($moved) {
                    PaliContentService::paragraphChanged(...$before, indexChanged: true, mayRemoveBook: $hadText);
                }
            });
        };
        $removed = function (Sentence $sentence) {
            $now = [(int) $sentence->book_id, (int) $sentence->paragraph, (string) $sentence->channel_uid];
            DB::afterCommit(fn () => PaliContentService::paragraphChanged(...$now, indexChanged: true, mayRemoveBook: true));
        };
        static::created($created);
        static::updated($updated);
        static::deleted($removed);
        static::forceDeleted($removed);
    }

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'fork_at',
    ];

    /**
     * 获取句子所属的频道
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_uid', 'uid');
    }

    public function scopeType($query, $type)
    {
        return $query->whereHas('channel', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeNissaya($query)
    {
        return $query->whereHas('channel', function ($q) {
            $q->where('type', 'nissaya');
        });
    }

    public function scopeLanguage($query, $language)
    {
        return $query->whereHas('channel', function ($q) use ($language) {
            $q->where('lang', $language);
        });
    }
}
