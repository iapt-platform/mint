<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $casts = [
        'id' => 'string'
    ];
    protected $fillable = ['email', 'id'];
}
