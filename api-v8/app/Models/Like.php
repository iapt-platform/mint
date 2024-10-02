<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $fillable = ['type' , 'target_id', 'target_type','user_id','context'];
    protected $casts = [
            'id' => 'string'
        ];
}
