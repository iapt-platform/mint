<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressChapter extends Model
{
    use HasFactory;
	protected $fillable = ['book' , 'book', 'channel_id','lang'=>'en',
                            'all_trans','public','progress',
                            'title','created_at','updated_at'];
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
        return $this->belongsToMany('App\Models\Tag','tag_maps','anchor_id','tag_id');
    }

    public function channel()
    {
        return $this->hasOne('App\Models\Channel', 'uid', 'channel_id'); //参数一:需要关联的子表类名,前面必须加上命名空间  参数二:子表关联父表的字段  参数三:父表关联子表的字段
    }

    public function views()
    {
        return $this->hasMany('App\Models\View', 'target_id', 'uid');
    }
}
