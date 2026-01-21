<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProgressChapter extends Model
{
    use HasFactory;
    protected $fillable = [
        'book',
        'book',
        'channel_id',
        'lang' => 'en',
        'all_trans',
        'public',
        'progress',
        'title',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'uid' => 'string'
    ];
    protected $primaryKey = 'uid';

    //protected $dateFormat = 'U';

    public function tagid()
    {
        return $this->hasOne('App\Models\TagMap', 'anchor_id', 'uid'); //参数一:需要关联的子表类名,前面必须加上命名空间  参数二:子表关联父表的字段  参数三:父表关联子表的字段
    }

    public function tags()
    {
        return $this->belongsToMany(
            'App\Models\Tag',
            'tag_maps',
            'anchor_id',
            'tag_id',
            'uid'
        );
    }
    /**
     * 关联到 Channel 模型
     * channel_id 关联到 channel 表的 uid 字段
     */
    public function channel()
    {
        return $this->hasOne(Channel::class, 'uid', 'channel_id');
    }
    public function views()
    {
        return $this->hasMany('App\Models\View', 'target_id', 'uid');
    }

    // 访问器格式化 created_at 字段
    public function getFormattedCreateAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
        // 你也可以使用其他格式：format('d/m/Y'), format('Y年m月d日') 等
    }
    public function getFormattedUpdatedAtAttribute()
    {
        //return Carbon::parse($value)->format('Y-m-d H:i:s');
        return $this->updated_at->format('Y年m月d日 H:i');
        // 你也可以使用其他格式：format('d/m/Y'), format('Y年m月d日') 等
    }
}
