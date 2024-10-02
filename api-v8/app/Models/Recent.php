<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recent extends Model
{
    use HasFactory;
    protected $casts = [
        'id' => 'string'
    ];
	protected $fillable = ['id','type','article_id','user_uid'];

}
