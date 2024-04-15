<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $casts = [
        'id' => 'string'
    ];
    public  $incrementing = false;

    protected $dates = [
        'created_at',
        'updated_at',
        'start_at',
        'end_at',
        'sign_up_start_at',
        'sign_up_end_at',
    ];

}
