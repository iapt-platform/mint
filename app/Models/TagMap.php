<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagMap extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string'
    ];
	protected $fillable = ['table_name' , 'anchor_id', 'tag_id'];
    use HasFactory;

    public function progresschapter()
    {
        return $this->belongsTo('App\Models\ProgressChapter', 'anchor_id', 'uid'); //参数一:需要关联的父表类名,前面必须加上命名空间  注意:参数二:子表关联父表的字段 参数三:父表关联子表的字段
    }

    public function tags()
    {
        return $this->hasOne('App\Models\Tag', 'tag_id', 'id');
    }
}
