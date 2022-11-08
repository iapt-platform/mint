<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    protected $fillable = ['name','summary','type','lang','status','updated_at','created_at'];

    protected $primaryKey = 'uid';
    protected $casts = [
        'uid' => 'string'
    ];
    public  $incrementing = false;
}
