<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;

    protected $casts = [
    'id' => 'string',
    'target_id' => 'string'
    ];

    protected $fillable = ['target_type' , 'target_id' , 'user_id', 'user_ip'];
}
