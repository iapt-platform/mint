<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $primaryKey = 'uid';
    protected $casts = [
        'uid' => 'string'
    ];
    protected $keyType = 'string';
    public  $incrementing = false;
}
