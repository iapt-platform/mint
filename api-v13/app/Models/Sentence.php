<?php

namespace App\Models;

use App\Services\PaliContentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected static function booted(): void
    {
        $forget = function (Sentence $sentence) {
            PaliContentService::forgetParagraph(
                (int) $sentence->book_id,
                (int) $sentence->paragraph,
                (string) $sentence->channel_uid
            );
        };
        static::saved($forget);
        static::deleted($forget);
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
