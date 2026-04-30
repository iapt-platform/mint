<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $primaryKey = 'uid';
    protected $casts = [
        'uid' => 'string'
    ];

    protected $fillable = ['id'];
}
