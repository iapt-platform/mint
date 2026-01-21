<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string'
    ];
    protected $fillable = ['name', 'owner_id'];

    use HasFactory;

    /**
     * 关联到ProgressChapter
     */
    public function chapters()
    {
        return $this->belongsToMany('App\Models\ProgressChapter', 'tag_maps', 'tag_id', 'anchor_id');
    }

    // 定义与 TagMap 模型的一对多关系
    public function tag_maps()
    {
        return $this->hasMany(TagMap::class);
    }
}
