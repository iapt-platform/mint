<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DhammaTerm extends Model
{
    use HasFactory;

    protected $primaryKey = 'guid';
    protected $casts = [
        'guid' => 'string'
    ];
    public  $incrementing = false;

}
