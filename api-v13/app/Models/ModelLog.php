<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelLog extends Model
{
    use HasFactory;
    protected $primaryKey = 'uid';
    protected $casts = [
        'uid' => 'string'
    ];
    protected $dates = [
        'request_at'
    ];
}
