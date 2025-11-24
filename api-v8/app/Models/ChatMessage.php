<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'chat_id',
        'parent_id',
        'session_id',
        'role',
        'content',
        'model_name',
        'tool_calls',
        'tool_call_id',
        'is_active'
    ];
    protected $dates = [
        'deleted_at'
    ];
    protected $casts = [
        'id' => 'string',
        'tool_calls' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
            if (empty($model->session_id)) {
                $model->session_id = (string) Str::uuid();
            }
        });
    }
    /**
     * 关联聊天
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
    /**
     * 关联父消息
     */
    public function parent()
    {
        return $this->belongsTo(ChatMessage::class, 'parent_id', 'uid');
    }
    /**
     * 关联子消息
     */
    public function children()
    {
        return $this->hasMany(ChatMessage::class, 'parent_id', 'uid');
    }
    /**
     * 关联激活状态的子消息
     */
    public function activeChildren()
    {
        return $this->hasMany(ChatMessage::class, 'parent_id', 'uid')
            ->where('is_active', true);
    }
    /**
     * Scope: 只查询激活状态的消息
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: 根据角色查询
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: 根据会话ID查询
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope: 根据聊天ID查询
     */
    public function scopeByChat($query, string $chatId)
    {
        return $query->where('chat_id', $chatId);
    }

    /**
     * 获取消息路径（从根到当前消息）
     */
    public function getPath(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * 获取消息树的所有叶子节点
     */
    public function getLeaves(): \Illuminate\Database\Eloquent\Collection
    {
        return ChatMessage::where('chat_id', $this->chat_id)
            ->whereNotNull('parent_id')
            ->whereDoesntHave('children')
            ->get();
    }

    /**
     * 获取Token使用情况
     */
    public function getTokenUsage(): ?array
    {
        return $this->metadata['token_usage'] ?? null;
    }

    /**
     * 获取生成参数
     */
    public function getGenerationParams(): ?array
    {
        return $this->metadata['generation_params'] ?? null;
    }

    /**
     * 设置Token使用情况
     */
    public function setTokenUsage(array $tokenUsage): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['token_usage'] = $tokenUsage;
        $this->update(['metadata' => $metadata]);
    }

    /**
     * 设置生成参数
     */
    public function setGenerationParams(array $params): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['generation_params'] = $params;
        $this->update(['metadata' => $metadata]);
    }

    public function siblings()
    {
        return $this->where('parent_id', $this->parent_id)
            ->where('role', $this->role)
            ->where('id', '!=', $this->id);
    }


    public function getRouteKeyName()
    {
        return 'uid';
    }
}
