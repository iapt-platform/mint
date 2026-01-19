<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public  $incrementing = true;

    // 可选：定义反向关联
    public function channels()
    {
        return $this->hasMany(Channel::class, 'owner_id', 'userid');
    }
}
