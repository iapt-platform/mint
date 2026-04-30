<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // ← 加这行
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'title',
        'user_id'
    ];

    protected $casts = [
        'id' => 'string',
        'uid' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    protected $primaryKey = 'uid';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
        });

        // 级联软删除：删除Chat时同时软删除所有相关消息
        static::deleting(function ($chat) {
            if ($chat->isForceDeleting()) {
                // 强制删除时，物理删除所有消息
                $chat->messages()->forceDelete();
            } else {
                // 软删除时，级联软删除所有消息
                $chat->messages()->delete();
            }
        });

        // 恢复Chat时同时恢复所有相关消息
        static::restoring(function ($chat) {
            $chat->messages()->withTrashed()->restore();
        });
    }
    // 使用 uid 作为路由键
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function getRouteKeyName()
    {
        return 'uid';
    }

    /**
     * 获取根消息（没有parent_id的消息）
     */
    public function rootMessages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_id', 'uid')
            ->whereNull('parent_id')
            ->where('is_active', true);
    }

    /**
     * 获取token使用统计
     */
    public function getTokenStats()
    {
        return $this->messages()
            ->where('role', 'assistant')
            ->whereNotNull('metadata')
            ->selectRaw('
                       SUM(JSON_EXTRACT(metadata, "$.token_usage.total_tokens")) as total_tokens,
                       SUM(JSON_EXTRACT(metadata, "$.token_usage.prompt_tokens")) as prompt_tokens,
                       SUM(JSON_EXTRACT(metadata, "$.token_usage.completion_tokens")) as completion_tokens,
                       AVG(JSON_EXTRACT(metadata, "$.performance.response_time_ms")) as avg_response_time
                   ')
            ->first();
    }

    /**
     * 批量软删除多个聊天
     */
    public static function batchSoftDelete(array $chatIds): int
    {
        return static::whereIn('uid', $chatIds)->delete();
    }

    /**
     * 批量恢复多个聊天
     */
    public static function batchRestore(array $chatIds): int
    {
        return static::withTrashed()->whereIn('uid', $chatIds)->restore();
    }
}
